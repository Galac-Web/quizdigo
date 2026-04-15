const transport = {
    async get(url) {
        const response = await fetch(url);
        return response.json();
    },
    async post(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data),
        });
        return response.json();
    },
    async upload(url, formData) {
        const response = await fetch(url, {
            method: 'POST',
            body: formData,
        });
        return response.json();
    }
};

export default transport;