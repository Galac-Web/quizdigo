export class FormDataHandler {
    constructor(formSelector, options = {}) {
        this.form = document.querySelector(formSelector);
        if (!this.form) console.warn("Форма не найдена, будет использоваться пустая FormData");
        this.options = options;
        this.formData = new FormData(this.form || undefined);
    }

    collectData() {
        if (this.form) {
            this.formData = new FormData(this.form);
        }
        return this;
    }

    addParam(name, value) {
        this.formData.append(name, value);
        return this;
    }

    removeParam(name) {
        this.formData.delete(name);
        return this;
    }

    validate() {
        if (!this.form) return true; // Если формы нет, пропускаем валидацию

        let isValid = true;
        this.form.querySelectorAll(".error").forEach(el => el.classList.remove("error"));

        this.form.querySelectorAll("[required]").forEach(input => {
            if (!input.value.trim()) {
                input.classList.add("error");
                isValid = false;
            }
        });

        this.form.querySelectorAll("[type='email']").forEach(input => {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(input.value.trim())) {
                input.classList.add("error");
                isValid = false;
            }
        });

        this.form.querySelectorAll("[type='number']").forEach(input => {
            if (isNaN(input.value.trim()) || input.value.trim() === "") {
                input.classList.add("error");
                isValid = false;
            }
        });

        this.form.querySelectorAll("[type='password']").forEach(input => {
            if (input.value.length < 6) {
                input.classList.add("error");
                isValid = false;
            }
        });

        this.form.querySelectorAll("[type='tel']").forEach(input => {
            const phonePattern = /^\+?\d{10,15}$/;
            if (!phonePattern.test(input.value.trim())) {
                input.classList.add("error");
                isValid = false;
            }
        });

        this.form.querySelectorAll("[type='date']").forEach(input => {
            if (!input.value.trim()) {
                input.classList.add("error");
                isValid = false;
            }
        });

        return isValid;
    }

    async send(url, validate = true) {
        if (validate && !this.validate()) {
            console.error("Форма содержит ошибки");
            return Promise.reject("Форма содержит ошибки");
        }

        try {
            const response = await fetch(url, {
                method: "POST",
                body: this.formData,
                headers: this.options.headers || {},
            });

            if (!response.ok) {
                throw new Error(`Ошибка HTTP: ${response.status}`);
            }

            const contentType = response.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                return await response.json();
            } else {
                return await response.text();
            }
        } catch (error) {
            console.error("Ошибка отправки данных:", error);
            throw error;
        }
    }
}
