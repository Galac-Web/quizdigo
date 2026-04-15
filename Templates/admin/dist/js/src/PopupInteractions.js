import { PopupTemplates } from './PopupTemplates.js';

export class PopupInteractions {
    constructor({ dom, popup, store, canvasRenderer, slideRenderer, storage }) {
        this.dom = dom;
        this.popup = popup;
        this.store = store;
        this.canvasRenderer = canvasRenderer;
        this.slideRenderer = slideRenderer;
        this.storage = storage;
    }

    bind() {
        document.addEventListener('click', this.handleClick);
    }

    handleClick = async (e) => {
        const trigger = e.target.closest('[data-action]');
        if (!trigger) return;

        const action = trigger.dataset.action;

        if (action === 'open-test-popup') {
            e.preventDefault();

            this.popup.open({
                title: 'Popup Global',
                content: `
          <div style="font-weight:800;color:#334155">
            Popup-ul global funcționează corect.
          </div>
        `,
                footerButtons: [
                    {
                        label: 'Cancel',
                        className: 'btn btn-gray',
                        close: true,
                    },
                    {
                        label: 'OK',
                        className: 'btn btn-blue',
                        onClick: () => {
                            this.popup.close();
                        }
                    }
                ]
            });

            return;
        }

        if (action === 'open-theme-popup') {
            e.preventDefault();

            const template = PopupTemplates.themeLibrary(this.store.getThemesFromConfig());

            this.popup.open({
                title: template.title,
                content: template.content,
                footerButtons: template.footerButtons,
            });

            return;
        }

        if (action === 'select-theme') {
            e.preventDefault();

            const theme = {
                id: trigger.dataset.themeId || '',
                name: trigger.dataset.themeName || 'standart',
                url: trigger.dataset.themeUrl || '',
            };

            this.store.applyTheme(theme);
            this.canvasRenderer.render();
            this.slideRenderer.renderSlides();
            this.storage.save();
            this.popup.close();

            return;
        }

        if (action === 'open-popup' && trigger.dataset.popup === 'type') {
            e.preventDefault();

            const template = PopupTemplates.questionTypes(this.store.getQuestionTypes());

            this.popup.open({
                title: template.title,
                content: template.content,
                footerButtons: template.footerButtons,
            });

            return;
        }

        if (action === 'select-question-type') {
            e.preventDefault();

            const typeId = trigger.dataset.typeId;
            if (!typeId) return;

            this.store.applyConfiguredQuestionType(typeId);
            this.canvasRenderer.render();
            this.slideRenderer.renderSlides();
            this.storage.save();
            this.popup.close();

            return;
        }
    };
}
