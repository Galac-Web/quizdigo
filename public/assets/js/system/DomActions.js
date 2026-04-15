class DomActions {
    constructor() {
        this.actionSelector = '[data-action]';
    }

    init() {
        document.body.addEventListener('click', e => {
            const actionEl = e.target.closest(this.actionSelector);
            if (!actionEl) return;

            const action = actionEl.dataset.action;
            const target = actionEl.closest('.col, .row, .card');

            this.handleAction(action, target, actionEl);
        });

        console.log('[DomActions] Activat global pe toate butoanele cu data-action.');
    }

    handleAction(action, element, trigger) {
        console.log(`[DomActions] Acțiune: ${action}`, { element, trigger });

        switch (action) {
            case 'delete-client':
                this.deleteClient(element);
                break;
            case 'add-firm':
                this.submitFirmForm(trigger);
                break;

            case 'highlight':
                element.style.background = '#ffe0e0';
                break;

            case 'preview':
                this.previewData(element);
                break;

            default:
                console.warn(`[DomActions] Acțiune necunoscută: ${action}`);
        }
    }

    deleteClient(el) {
        const clientId = el.dataset.id;
        if (!clientId) return;

        fetch(`/api/client/delete/${clientId}`, { method: 'DELETE' })
            .then(res => res.json())
            .then(() => {
                el.remove();
                console.log(`Client ${clientId} șters.`);
            });
    }
    submitFirmForm(trigger) {
        const form = trigger.closest('form');

        if (!form) {
            console.warn('[DomActions] Nu s-a găsit formularul pentru adăugare firmă.');
            return;
        }

        const endpoint = form.dataset.endpoint;
        const method = (form.dataset.method || 'POST').toUpperCase();
        const successMsg = form.dataset.success || 'Trimis cu succes!';

        const data = {};
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            data[input.name] = input.value;
        });

        fetch(endpoint, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
            .then(res => res.json())
            .then(json => {
                console.log('[DomActions] Firmă adăugată:', json);
                alert(successMsg);
                form.reset();
            })
            .catch(err => {
                console.error('[DomActions] Eroare la trimiterea formularului:', err);
                alert('A apărut o eroare la salvare.');
            });
    }
    previewData(el) {
        alert('Previzualizare: ' + el.innerText);
    }
}

export default new DomActions();
