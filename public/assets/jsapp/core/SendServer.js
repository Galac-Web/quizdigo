export class SendServer {
    static TIMEOUT = 30000; // 30 secunde timeout
    static EVENTS = {
        UPLOAD_START: 'upload:start',
        UPLOAD_PROGRESS: 'upload:progress',
        UPLOAD_COMPLETE: 'upload:complete',
        UPLOAD_ERROR: 'upload:error'
    };

    #url;
    #formData;
    #options;
    #xhr = null;

    /**
     * @param {string} url - URL-ul pentru trimiterea datelor
     * @param {Object} options - Opțiuni pentru request
     *  - timeout   (ms)
     *  - headers   (Object)
     *  - retries   (număr încercări suplimentare)
     *  - retryDelay (ms, întârziere între încercări)
     */
    constructor(url, options = {}) {
        if (!url) throw new Error('URL is required');

        this.#url = url;
        this.#formData = new FormData();
        this.#options = {
            timeout: options.timeout || SendServer.TIMEOUT,
            headers: options.headers || {},
            retries: options.retries || 0,
            retryDelay: options.retryDelay || 1000
        };
    }

    /**
     * Adaugă date în FormData
     * @param {string} name - Numele câmpului
     * @param {*} value - Valoarea câmpului
     * @returns {SendServer} - Instanța curentă pentru chaining
     */
    addData(name, value) {
        if (!name) throw new Error('Field name is required');

        if (value instanceof File || value instanceof Blob) {
            this.#formData.append(name, value, value.name);
        } else {
            this.#formData.append(name, value);
        }

        return this;
    }

    /**
     * Adaugă mai multe date dintr-un obiect
     * @param {Object} dataObject - Obiectul cu date
     * @returns {SendServer} - Instanța curentă pentru chaining
     */
    addMultipleData(dataObject = {}) {
        Object.entries(dataObject).forEach(([name, value]) => {
            this.addData(name, value);
        });

        return this;
    }

    /**
     * Emite evenimente custom
     * @private
     */
    #emitEvent(eventName, detail = {}) {
        window.dispatchEvent(
            new CustomEvent(eventName, {
                detail: {
                    url: this.#url,
                    ...detail
                }
            })
        );
    }

    /**
     * Gestionează progresul încărcării
     * @private
     */
    #handleProgress = (event) => {
        if (event.lengthComputable) {
            const progress = (event.loaded / event.total) * 100;
            this.#emitEvent(SendServer.EVENTS.UPLOAD_PROGRESS, {
                progress,
                loaded: event.loaded,
                total: event.total
            });
        }
    };

    /**
     * Trimite cererea cu retry logic
     * @private
     */
    async #sendWithRetry(attempt = 1) {
        try {
            return await this.#sendRequest();
        } catch (error) {
            if (attempt <= this.#options.retries) {
                // backoff simplu: delay * attempt
                await new Promise((resolve) =>
                    setTimeout(resolve, this.#options.retryDelay * attempt)
                );
                return this.#sendWithRetry(attempt + 1);
            }
            throw error;
        }
    }

    /**
     * Creează și configurează XHR
     * @private
     */
    #createXhr() {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', this.#url, true);

        // Timeout în ms
        xhr.timeout = this.#options.timeout;

        // Setăm headers custom (nu setăm Content-Type pentru FormData!)
        Object.entries(this.#options.headers).forEach(([key, value]) => {
            xhr.setRequestHeader(key, value);
        });

        // Progres upload
        xhr.upload.onprogress = this.#handleProgress;

        return xhr;
    }

    /**
     * Trimite cererea efectivă (XMLHttpRequest + FormData)
     * @private
     * @returns {Promise<Object>} - Răspuns JSON parsat
     */
    #sendRequest() {
        return new Promise((resolve, reject) => {
            this.#xhr = this.#createXhr();

            this.#emitEvent(SendServer.EVENTS.UPLOAD_START);

            this.#xhr.onreadystatechange = () => {
                if (this.#xhr.readyState !== XMLHttpRequest.DONE) return;

                const status = this.#xhr.status;
                const rawResponse = this.#xhr.responseText ?? '';

                // Resetăm referința la xhr după finalizare
                const xhrRef = this.#xhr;
                this.#xhr = null;

                if (status === 0) {
                    // Aborted sau network error
                    const err = new Error('Request aborted sau eroare de rețea.');
                    this.#emitEvent(SendServer.EVENTS.UPLOAD_ERROR, {
                        error: err,
                        status,
                        rawResponse
                    });
                    reject(err);
                    return;
                }

                if (status < 200 || status >= 300) {
                    const err = new Error(`HTTP error! status: ${status}`);
                    this.#emitEvent(SendServer.EVENTS.UPLOAD_ERROR, {
                        error: err,
                        status,
                        rawResponse
                    });
                    reject(err);
                    return;
                }

                // Încercăm să parsăm JSON
                let data;
                try {
                    data = rawResponse ? JSON.parse(rawResponse) : {};
                } catch (e) {
                    const err = new Error('Răspuns invalid de la server (nu e JSON valid).');
                    this.#emitEvent(SendServer.EVENTS.UPLOAD_ERROR, {
                        error: err,
                        status,
                        rawResponse
                    });
                    reject(err);
                    return;
                }

                // Emit succes
                this.#emitEvent(SendServer.EVENTS.UPLOAD_COMPLETE, {
                    status,
                    data
                });

                resolve(data);
            };

            this.#xhr.ontimeout = () => {
                const err = new Error('Request timeout.');
                this.#emitEvent(SendServer.EVENTS.UPLOAD_ERROR, {
                    error: err,
                    status: 0
                });
                this.#xhr.abort();
                this.#xhr = null;
                reject(err);
            };

            this.#xhr.onerror = () => {
                const err = new Error('Eroare de rețea.');
                this.#emitEvent(SendServer.EVENTS.UPLOAD_ERROR, {
                    error: err,
                    status: this.#xhr.status
                });
                this.#xhr = null;
                reject(err);
            };

            // Pornim upload-ul
            this.#xhr.send(this.#formData);
        });
    }

    /**
     * Trimite datele la server
     * @returns {Promise<Object>} - Promise cu răspunsul JSON de la server
     */
    async send() {
        return this.#sendWithRetry();
    }

    /**
     * Anulează cererea curentă
     */
    abort() {
        if (this.#xhr) {
            this.#xhr.abort();
            this.#xhr = null;
        }
    }

    /**
     * Curăță datele din formular
     */
    clear() {
        this.#formData = new FormData();
    }

    /**
     * Verifică dacă există o cerere în curs
     */
    isInProgress() {
        return !!this.#xhr;
    }
}
