import Router from "./core/Router.js";

const router = new Router();

const PAGES = {
    rooms: "./pages/Rooms.js",
};

let currentPageInstance = null;

async function loadPage(routeName) {
    if (!routeName || !PAGES[routeName]) return;

    if (currentPageInstance?.destroy) currentPageInstance.destroy();

    const { default: PageClass } = await import(PAGES[routeName]);
    const page = new PageClass();
    if (page.init) await page.init();
    currentPageInstance = page;
}

document.addEventListener("DOMContentLoaded", () => {
    loadPage(router.page);
});
