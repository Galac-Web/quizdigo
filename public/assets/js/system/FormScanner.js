import transport from '../transport.js';

class FormScanner {
    constructor() {
        this.forms = [];
    }

    init() {
        this.forms = document.querySelectorAll('form[data-autosave]');
        this.forms.forEach(form => {
            form.addEventListener('submit', e => {
                e.preventDefault();
                this.handleSubmit(form);
            });
        });
        console.log(`[FormScanner] ${this.forms.length} formulare detectate.`);
    }

    async handleSubmit(form) {
        const endpoint = form.dataset.endpoint;
        const method = (form.dataset.method || 'POST').toUpperCase();
        const successMsg = form.dataset.success || 'Trimis cu succes!';

        if (!endpoint) {
            console.warn('[FormScanner] Lipsă `data-endpoint` pe formular.');
            return;
        }

        // Verificăm dacă formularul are fișiere
        const hasFiles = form.querySelector('input[type="file"]');

        try {
            let responseData;

            if (hasFiles) {
                const formData = new FormData(form);
                responseData = await transport.upload(endpoint, formData);
            } else {
                const data = {};
                const inputs = form.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    if (input.name) {
                        data[input.name] = input.value;
                    }
                });

                if (method === 'POST') {
                    responseData = await transport.post(endpoint, data);
                } else {
                    console.warn(`[FormScanner] Metoda ${method} nu este implementată în transport.`);
                    return;
                }
            }

            console.log('[FormScanner] Succes:', responseData);
            alert(successMsg);

        } catch (err) {
            console.error('[FormScanner] Eroare la trimiterea formularului:', err);
        }
    }
}

export default new FormScanner();
