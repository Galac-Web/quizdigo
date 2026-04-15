export class GlobalPopup {
    constructor(dom) {
        this.dom = dom;
        this.state = {
            isOpen: false,
            currentName: null,
        };

        this.handleKeydown = this.handleKeydown.bind(this);
        this.handleClick = this.handleClick.bind(this);
    }

    init() {
        if (!this.dom.popup) return;

        document.addEventListener('keydown', this.handleKeydown);
        this.dom.popup.addEventListener('click', this.handleClick);
    }

    handleKeydown(e) {
        if (e.key === 'Escape' && this.state.isOpen) {
            this.close();
        }
    }

    handleClick(e) {
        if (!this.dom.popup) return;

        if (e.target === this.dom.popup) {
            this.close();
            return;
        }

        const closeBtn = e.target.closest('[data-popup-close]');
        if (closeBtn) {
            this.close();
        }
    }

    setTitle(title = '') {
        if (!this.dom.popupTitle) return;
        this.dom.popupTitle.textContent = String(title || '');
    }

    setContent(content = '') {
        if (!this.dom.popupBody) return;

        if (typeof content === 'string') {
            this.dom.popupBody.innerHTML = content;
            return;
        }

        if (content instanceof HTMLElement) {
            this.dom.popupBody.innerHTML = '';
            this.dom.popupBody.appendChild(content);
            return;
        }

        this.dom.popupBody.innerHTML = '';
    }

    setFooter(buttons = []) {
        const foot = this.dom.qs('#popup-foot', this.dom.popup);
        if (!foot) return;

        foot.innerHTML = '';

        if (!Array.isArray(buttons) || !buttons.length) {
            foot.style.display = 'none';
            return;
        }

        foot.style.display = 'flex';

        buttons.forEach((btnConfig) => {
            const btn = document.createElement('button');
            btn.type = btnConfig.type || 'button';
            btn.className = btnConfig.className || 'btn btn-gray';
            btn.textContent = btnConfig.label || 'Button';

            if (btnConfig.close === true) {
                btn.setAttribute('data-popup-close', '');
            }

            if (typeof btnConfig.onClick === 'function') {
                btn.addEventListener('click', btnConfig.onClick);
            }

            foot.appendChild(btn);
        });
    }

    open({ name = null, title = 'Popup', content = '', footerButtons = null } = {}) {
        if (!this.dom.popup) return;

        this.state.isOpen = true;
        this.state.currentName = name;

        this.setTitle(title);
        this.setContent(content);

        this.setFooter(
            footerButtons || [
                {
                    label: 'Închide',
                    className: 'btn btn-gray',
                    close: true,
                }
            ]
        );

        this.dom.popup.setAttribute('aria-hidden', 'false');
        document.body.classList.add('popup-open');
    }

    close() {
        if (!this.dom.popup) return;

        this.state.isOpen = false;
        this.state.currentName = null;

        this.dom.popup.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('popup-open');

        this.setTitle('Popup');
        this.setContent('');
        this.setFooter([
            {
                label: 'Închide',
                className: 'btn btn-gray',
                close: true,
            }
        ]);
    }

    isOpen() {
        return this.state.isOpen;
    }

    getCurrentName() {
        return this.state.currentName;
    }
}