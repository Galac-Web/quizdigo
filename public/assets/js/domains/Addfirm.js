import formScanner from '../system/FormScanner.js';
import DomActions from '../system/DomActions.js'; // sau unde ai DomActions

class AddFirm {
    constructor() {
        this.status = 'initiat';
        console.log('[FirmModule] Constructor apelat, status:', this.status);
    }

    init(subPage) {
        console.log("✅ Modul `FirmModule` inițializat.");
        DomActions.init();        // ← important
        formScanner.init();       // ← activează autosave pe formulare

        if (subPage === 'edit') {
            this.initEditPage();
        } else {
            this.initGeneral();
        }
    }

    initGeneral() {
        console.log("Modul `addfirm` activat.");
    }

    initEditPage() {
        console.log("✏️ Inițializare pentru pagina de editare firmă.");
        // cod specific
    }
}

export default new AddFirm();
