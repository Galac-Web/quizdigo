import { PopupTemplates } from './PopupTemplates.js';

export class SlideInteractions {
    constructor({ dom, store, renderer, canvas, storage, popup }) {
        this.dom = dom;
        this.store = store;
        this.renderer = renderer;
        this.canvas = canvas;
        this.storage = storage;
        this.popup = popup;
    }

    bind() {
        document.addEventListener('click', this.handleClick);
    }

    syncUI() {
        if (this.renderer?.renderSlides) this.renderer.renderSlides();
        if (this.canvas?.render) this.canvas.render();
        if (this.storage?.save) this.storage.save();
    }

    handleClick = (e) => {
        const trigger = e.target.closest('[data-action]');
        if (!trigger) return;

        const action = trigger.dataset.action;
        const id = Number(trigger.dataset.id);

        if (action === 'add-slide') {
            e.preventDefault();

            const template = PopupTemplates.chooseSlideType();

            this.popup.open({
                title: template.title,
                content: template.content,
                footerButtons: template.footerButtons,
            });

            return;
        }

        if (action === 'create-slide-quiz') {
            e.preventDefault();
            this.store.addQuizSlide();
            this.popup.close();
            this.syncUI();
            return;
        }

        if (action === 'create-slide-media') {
            e.preventDefault();
            this.store.addMediaSlide();
            this.popup.close();
            this.syncUI();
            return;
        }

        if (action === 'select-slide') {
            e.preventDefault();
            if (!Number.isFinite(id)) return;

            this.store.selectSlide(id);
            this.syncUI();
            return;
        }

        if (action === 'delete-slide') {
            e.preventDefault();
            e.stopPropagation();
            if (!Number.isFinite(id)) return;

            const deleted = this.store.deleteSlide(id);
            if (!deleted) return;

            this.syncUI();
            return;
        }

        if (action === 'duplicate-slide') {
            e.preventDefault();
            e.stopPropagation();
            if (!Number.isFinite(id)) return;

            const duplicated = this.store.duplicateSlide(id);
            if (!duplicated) return;

            this.syncUI();
            return;
        }
    };
}