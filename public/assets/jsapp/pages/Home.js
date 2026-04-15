import { SendServer } from "../core/SendServer.js";

class Home {
    constructor() {
        this.init();
    }

    init() {
        console.log('yes');
        document.querySelectorAll('.actionblock').forEach(button => {
            button.addEventListener('click', async (e) => {
                e.preventDefault();
                const status = button.dataset.status;
                const id = button.dataset.id;
                this.submitUserForm(status,id);

            });
        });
    }
    async submitUserForm(status,id) {
        const sendServer = new SendServer('/addusers');
        sendServer.addData('type_product', 'setstaus');
        sendServer.addData('id', id);
        sendServer.addData('status', status);


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
                document.querySelector(`.blk${id}`).textContent = status;

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

export default Home;
