import { eventBus } from "./EventBus.js";

class Modal {
    constructor({ title = "Modal Title", content = "Modal Content", width = "400px" }) {
        this.title = title;
        this.content = content;
        this.width = width;
        this.modal = null;
        this.createModal();
    }

    createModal() {
        this.modal = document.createElement("div");
        this.modal.classList.add("custom-modal");
        this.modal.innerHTML = `
            <div class="custom-modal-overlay"></div>
            <div class="custom-modal-content" style="width: ${this.width};">
                <div class="custom-modal-header">
                    <h2>${this.title}</h2>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="custom-modal-body">${this.content}</div>
            </div>
        `;

        document.body.appendChild(this.modal);
        this.addEventListeners();
    }

    addEventListeners() {
        this.modal.querySelector(".close-modal").addEventListener("click", () => this.close());
        this.modal.querySelector(".custom-modal-overlay").addEventListener("click", () => this.close());
    }

    open() {
        this.modal.classList.add("active");
    }

    close() {
        this.modal.classList.remove("active");
        setTimeout(() => this.modal.remove(), 300);
    }
}

eventBus.on("openModal", (data) => {
    const modal = new Modal(data);
    modal.open();
});

export default Modal;
