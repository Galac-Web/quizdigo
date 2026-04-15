// jsapp/core/Component.js

export class Component {
    /**
     * @param {string} rootSelector - CSS selector pentru containerul principal al componentei/paginii
     */
    constructor(rootSelector = "#app") {
        this.rootSelector = rootSelector;
        this.root = document.querySelector(rootSelector) || document.body;

        if (!document.querySelector(rootSelector)) {
            console.warn(
                `[Component] Root selector "${rootSelector}" nu a fost găsit în DOM. ` +
                `Se folosește <body> ca fallback.`
            );
        }

        /**
         * Listă internă de listeneri pentru a-i putea curăța în destroy()
         * @type {Array<{element: Element|Window|Document, type: string, handler: Function, options: any}>}
         */
        this._listeners = [];
    }

    /**
     * Lifecycle hook — se cheamă după ce componenta este creată.
     * Pagina ta (Home, Profile, Rooms etc.) va suprascrie metoda asta.
     */
    async init() {
        // de suprascris în clasele copil
    }

    /**
     * Lifecycle hook — se cheamă înainte ca componenta să fie distrusă.
     * Aici curățăm automat toți listenerii DOM înregistrați prin this.on()
     */
    destroy() {
        this._listeners.forEach(({ element, type, handler, options }) => {
            element.removeEventListener(type, handler, options);
        });
        this._listeners = [];
    }

    /**
     * Helper pentru query în interiorul root-ului componentei
     */
    find(selector) {
        return this.root ? this.root.querySelector(selector) : null;
    }

    /**
     * Helper pentru querySelectorAll în interiorul root-ului
     */
    findAll(selector) {
        return this.root ? Array.from(this.root.querySelectorAll(selector)) : [];
    }

    /**
     * Înregistrează un event listener care va fi automat șters în destroy()
     * @param {Element|Window|Document} element
     * @param {string} type
     * @param {Function} handler
     * @param {Object|boolean} [options]
     */
    on(element, type, handler, options) {
        if (!element || !type || !handler) return;
        element.addEventListener(type, handler, options);
        this._listeners.push({ element, type, handler, options });
    }

    /**
     * Static helper — montează rapid o pagină
     * Exemplu: Component.mount(Profile, "#app");
     */
    static async mount(ComponentClass, rootSelector = "#app") {
        const instance = new ComponentClass(rootSelector);
        await instance.init();
        return instance;
    }
}

export default Component;
