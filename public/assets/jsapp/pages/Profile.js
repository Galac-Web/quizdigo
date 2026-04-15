import { SendServer } from "../core/SendServer.js";

class Profile {
    constructor() {
        this.init();
        this.addusers();
    }

    init() {
        console.log('ues');
        const submitBtn = document.getElementById('editsave');
        if (submitBtn) {
            submitBtn.addEventListener('click', () => this.submitUserForm());
        }

    }

    addusers(){
        console.
        document.querySelectorAll('.actionblock').forEach(button => {
            button.addEventListener('click', async (e) => {
                e.preventDefault();

                const status = button.dataset.status;
                console.log(status);
            });
        });
    }
    async submitUserForm() {
        const sendServer = new SendServer('/addusers');
        sendServer.addData('type_product', 'user_update');

        // Colectăm toate inputurile, selecturile și textele
        document.querySelectorAll('.app-form input, .app-form select, .app-form textarea').forEach(element => {
            const name = element.getAttribute('name');
            if (name) {
                sendServer.addData(name, element.value);
            }
        });

        try {
            const response = await sendServer.send();

            // Citim răspunsul o singură dată
            const responseText = await response.text();
            let data;

            try {
                // Încearcă să parseze JSON
                data = JSON.parse(responseText);
            } catch (e) {
                // Dacă nu e JSON, înseamnă că e o eroare HTML sau alt conținut
                console.error("Server response is not valid JSON:", responseText);
                alert("A server error occurred:\n\n" + responseText);
                return null;
            }

            if (data.success) {
                location.reload();
            } else {
                alert('Eroare: ' + (data.message || 'Salvarea a eșuat.'));
            }

            return data;

        } catch (error) {
            console.error("Eroare la trimiterea datelor:", error);
            alert("A apărut o eroare la trimitere. Verifică conexiunea sau încearcă din nou.");
            return null;
        }
    }

}

export default Profile;
