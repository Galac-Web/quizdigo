// pages/Rooms.js
import Component from "../core/Component.js";

class Rooms extends Component {
    constructor(rootSelector = "#app") {
        super(rootSelector);

        // endpoint-ul API pentru rooms
        this.apiUrl = "/public/crudrooms";

        // legăm metodele ca să păstrăm "this"
        this.handleCreateClick = this.handleCreateClick.bind(this);
        this.handleListClick = this.handleListClick.bind(this);
    }

    /**
     * init() – se cheamă automat din index.js după new Rooms()
     */
    async init() {
        // Elementul care conține cardurile cu rooms
        this.listContainer = document.getElementById("rooms-list");

        // Formular + buton din modalul de creare
        this.form = document.getElementById("create-room-form");
        this.createBtn = document.getElementById("create-room-submit");

        // Eveniment: click pe "Salvează Room"
        if (this.createBtn && this.form) {
            this.on(this.createBtn, "click", this.handleCreateClick);
        }

        // Eveniment: click pe butoanele din lista de rooms (delete etc.)
        if (this.listContainer) {
            this.on(this.listContainer, "click", this.handleListClick);
        }

        // La încărcarea paginii: luăm lista de rooms din backend
        await this.loadRooms();
    }

    /**
     * trimite request JSON la backend-ul rooms
     */
    async apiRequest(payload) {
        const response = await fetch(this.apiUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(payload),
        });

        const raw = await response.text();

        // detectăm răspuns HTML (eroare PHP / pagină, nu JSON)
        if (raw.trim().startsWith("<")) {
            console.error("[Rooms] Serverul a trimis HTML, nu JSON:", raw);
            this.toast("Serverul a răspuns cu o pagină HTML, nu JSON. Verifică /public/crudrooms.", "error");
            throw new Error("Invalid JSON (HTML received)");
        }

        let data;
        try {
            data = JSON.parse(raw);
        } catch (e) {
            console.error("[Rooms] JSON parse error:", e, raw);
            this.toast("Răspuns invalid de la server (nu e JSON valid).", "error");
            throw e;
        }

        if (!response.ok || data.success === false) {
            const msg = data.message || `Eroare HTTP (${response.status})`;
            this.toast(msg, "error");
            throw new Error(msg);
        }

        return data;
    }

    /**
     * Încarcă lista de rooms din BD și o randază
     */
    async loadRooms() {
        try {
            const res = await this.apiRequest({ type_product: "list" });
            const rooms = res.data || [];
            this.renderRooms(rooms);
        } catch (e) {
            console.error("[Rooms] Eroare la loadRooms:", e);
        }
    }

    /**
     * Randare carduri Rooms în containerul #rooms-list
     * (folosește aceeași logică ca exemplul tău de HTML)
     */
    renderRooms(rooms) {
        if (!this.listContainer) return;

        this.listContainer.innerHTML = "";

        if (!rooms || rooms.length === 0) {
            this.listContainer.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-info">
                        Nu există încă nicio cameră creată. Creează primul Room folosind butonul
                        <strong>"Creează Room nou"</strong>.
                    </div>
                </div>
            `;
            return;
        }

        rooms.forEach((room) => {
            const col = document.createElement("div");
            col.className = "col";

            const title = room.title || "(fără titlu)";
            const uid = room.room_uid || "-";
            const status = parseInt(room.status ?? 0, 10);
            const countdown = room.countdown_sec || 0;
            const duration = room.duration_sec || 0;

            const statusBadge =
                status === 1
                    ? '<span class="badge bg-success">Activă</span>'
                    : '<span class="badge bg-secondary">Închisă</span>';

            const durationInfo = `Countdown: ${countdown}s • Durată: ${duration}s`;
            const viewUrl = `/public/profilerooms?uid=${encodeURIComponent(uid)}`;

            col.innerHTML = `
                <div class="card radius-15">
                    <div class="card-body text-center">
                        <div class="p-4 border radius-15">
                            <img src="/Templates/admin/assets/corporation.png"
                                 width="110" height="110"
                                 class="rounded-circle shadow mb-3"
                                 alt="Icon room">

                            <h5 class="mb-1">
                                ${title}
                                <span class="ms-2">${statusBadge}</span>
                            </h5>

                            <p class="mb-1 text-muted small">UID: <code>${uid}</code></p>
                            <p class="text-muted small">${durationInfo}</p>

                            <div class="d-grid gap-2 mt-3">
                                <a href="${viewUrl}" class="btn btn-outline-info radius-15">
                                    <i class="bx bx-show"></i> Detalii / Start test
                                </a>
                                <button class="btn btn-outline-danger radius-15"
                                        data-action="delete-room"
                                        data-uid="${uid}">
                                    <i class="bx bx-trash"></i> Șterge
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            this.listContainer.appendChild(col);
        });

        // Card "Adaugă Room nou"
        const addCol = document.createElement("div");
        addCol.className = "col";
        addCol.innerHTML = `
            <div class="card radius-15 border-dashed">
                <div class="card-body text-center d-flex align-items-center justify-content-center" style="height: 100%;">
                    <a href="#" class="text-muted" data-bs-toggle="modal" data-bs-target="#createRoomModal">
                        <i class="bx bx-plus-circle fs-1"></i>
                        <div>Adaugă Room nou</div>
                    </a>
                </div>
            </div>
        `;
        this.listContainer.appendChild(addCol);
    }

    /**
     * Handler pentru click pe butonul "Salvează Room"
     */
    async handleCreateClick(e) {
        e.preventDefault();
        if (!this.form) return;

        const formData = new FormData(this.form);

        const payload = {
            type_product: "add",
            title: (formData.get("title") || "").toString(),
            countdown_sec: parseInt(formData.get("countdown_sec") || "10", 10),
            duration_sec: parseInt(formData.get("duration_sec") || "90", 10),
            status: parseInt(formData.get("status") || "1", 10),
            meta: formData.get("meta") || null,
        };

        if (!payload.title.trim()) {
            this.toast("Titlul este obligatoriu.", "error");
            return;
        }

        try {
            const res = await this.apiRequest(payload);
            this.toast(res.message || "Room creat cu succes.", "success");

            // Închidem modalul dacă Bootstrap este disponibil
            const modalEl = document.getElementById("createRoomModal");
            if (modalEl && window.bootstrap) {
                const modal = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
                modal.hide();
            }

            this.form.reset();
            await this.loadRooms();
        } catch (e) {
            console.error("[Rooms] Eroare la crearea room-ului:", e);
        }
    }

    /**
     * Handler pentru click în lista de rooms (delete)
     * Folosim event delegation
     */
    async handleListClick(e) {
        const target = e.target.closest("[data-action='delete-room']");
        if (!target) return;

        e.preventDefault();

        const uid = target.getAttribute("data-uid");
        if (!uid) return;

        if (!confirm("Ești sigur că vrei să ștergi acest Room?")) return;

        try {
            const res = await this.apiRequest({
                type_product: "delete",
                room_uid: uid,
            });
            this.toast(res.message || "Room șters.", "success");
            await this.loadRooms();
        } catch (e) {
            console.error("[Rooms] Eroare la ștergerea room-ului:", e);
        }
    }

    /**
     * Helper simplu pentru mesaje (poți înlocui cu toast-ul tău custom)
     */
    toast(message, type = "info") {
        // Dacă ai deja un sistem global de toast, îl poți apela aici
        // ex: window.showToast && window.showToast(message, type);
        alert(message);
    }
}

export default Rooms;
