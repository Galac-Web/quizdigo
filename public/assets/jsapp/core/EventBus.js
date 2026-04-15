class EventBus {
    constructor() {
        this.events = {};
    }

    /**
     * Abonează un callback la un eveniment
     */
    on(event, callback) {
        if (!event || typeof callback !== "function") {
            throw new Error("EventBus.on: invalid arguments");
        }

        if (!this.events[event]) {
            this.events[event] = new Set();
        }
        this.events[event].add(callback);

        // Returnăm o funcție pentru dezabonare directă
        return () => this.off(event, callback);
    }

    /**
     * Abonează un listener care se execută o singură dată
     */
    once(event, callback) {
        const off = this.on(event, (...args) => {
            off();
            callback(...args);
        });
        return off;
    }

    /**
     * Dezabonează
     */
    off(event, callback) {
        if (!this.events[event]) return;
        this.events[event].delete(callback);
        if (this.events[event].size === 0) {
            delete this.events[event];
        }
    }

    /**
     * Emite un eveniment (async safe)
     */
    emit(event, payload = null) {
        if (!this.events[event]) return;

        // Folosim array extern ca să evităm modificări în Set în timp ce iterăm
        [...this.events[event]].forEach(callback => {
            try {
                const result = callback(payload);
                if (result instanceof Promise) {
                    result.catch(err => {
                        console.error(`EventBus async error în '${event}':`, err);
                    });
                }
            } catch (err) {
                console.error(`EventBus error în '${event}':`, err);
            }
        });
    }

    /**
     * Curăță toate evenimentele
     */
    clear() {
        this.events = {};
    }
}

export const eventBus = new EventBus();
export default eventBus;
