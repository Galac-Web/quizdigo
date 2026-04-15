class Router {
    static PAGE_MAPPINGS = {
        rooms: "rooms",
        home: "home",
    };

    static DEFAULT_PAGE = "home";
    static DEFAULT_ENTRY = "index.php";
    static PAGE_NOT_FOUND = "N/A";

    static PAGE_PARAM = "page";

    #currentPage = Router.DEFAULT_PAGE;

    constructor() {
        this.#currentPage = this.#detectPage();
    }

    /**
     * 1) Determină pagina din URL
     * - dacă există ?page=...
     * - altfel DEFAULT_PAGE
     */
    #detectPage() {
        try {
            const params = new URLSearchParams(window.location.search);
            const requested = params.get(Router.PAGE_PARAM);

            const pageKey = requested || Router.DEFAULT_PAGE;

            if (this.isValidPage(pageKey)) {
                return Router.PAGE_MAPPINGS[pageKey];
            }

            console.warn(`[Router] Pagina cerută "${pageKey}" nu există.`);
            return Router.PAGE_NOT_FOUND;

        } catch (err) {
            console.error("[Router] Eroare la detectarea paginii:", err);
            return Router.DEFAULT_PAGE;
        }
    }

    /**
     * Getter pentru pagina curentă
     */
    get page() {
        return this.#currentPage;
    }

    /**
     * Verifică dacă pagina există în mapări
     */
    isValidPage(pageKey) {
        return Object.prototype.hasOwnProperty.call(Router.PAGE_MAPPINGS, pageKey);
    }

    /**
     * Adaugă mapare nouă (dinamic, la runtime)
     */
    addPageMapping(pageKey, routeName) {
        Router.PAGE_MAPPINGS[pageKey] = routeName;
    }

    /**
     * Modifică pagina curentă și opțional actualizează URL-ul
     */
    updateCurrentPage(newPageKey, options = {}) {
        const { pushState = false } = options;

        if (!this.isValidPage(newPageKey)) {
            console.warn(`[Router] Pagina "${newPageKey}" nu este validă.`);
            return false;
        }

        this.#currentPage = Router.PAGE_MAPPINGS[newPageKey];

        if (pushState) {
            const params = new URLSearchParams(window.location.search);
            params.set(Router.PAGE_PARAM, newPageKey);
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.pushState({ page: newPageKey }, "", newUrl);
        }

        return true;
    }

    /**
     * Verifică dacă URL-ul actual este index.php sau root
     */
    isIndexRoute(pathname = window.location.pathname) {
        const segment = pathname.split("/").pop();
        return segment === "" || segment === Router.DEFAULT_ENTRY;
    }
}

export default Router;
