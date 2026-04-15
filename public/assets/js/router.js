function toPascalCase(str) {
    return str
        .split('-')
        .map(s => s.charAt(0).toUpperCase() + s.slice(1))
        .join('');
}

const router = {
    async init() {
        const pageAttr = document.querySelector('.page-wrapper')?.dataset.page;
        if (!pageAttr) return;

        // Ex: "add-firm:edit" → ["add-firm", "edit"]
        const [rawModule, subPage] = pageAttr.split(':');

        const moduleName = toPascalCase(rawModule);  // "add-firm" → "AddFirm"

        try {
            const module = await import(`./domains/${moduleName}.js`);

            if (typeof module.default.init === 'function') {
                module.default.init(subPage || null);
            } else {
                console.warn(`Modulul '${moduleName}' nu are funcția init().`);
            }

        } catch (err) {
            console.warn(`Modulul JS '${moduleName}' nu a fost găsit în /domains.`, err);
        }
    }
};

export default router;