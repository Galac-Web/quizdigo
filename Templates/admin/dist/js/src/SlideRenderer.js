export class SlideRenderer {
    constructor(dom, store) {
        this.dom = dom;
        this.store = store;
    }

    escapeHtml(value = '') {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    getTypeLabel(type = '') {
        const map = {
            quiz: 'Quiz',
            media: 'Media',
            'true-false': 'True/False',
            'open-ended': 'Open',
            slider: 'Slider',
        };

        return map[type] || 'Quiz';
    }

    renderSlides() {
        if (!this.dom.slidesList) return;

        const slides = this.store.getSlides();
        const currentSlideId = this.store.getCurrentSlideId();

        this.dom.slidesList.innerHTML = '';

        slides.forEach((slide, index) => {
            const item = document.createElement('div');
            item.className = `slide ${slide.id === currentSlideId ? 'active' : ''}`;
            item.dataset.action = 'select-slide';
            item.dataset.id = String(slide.id);

            const bgUrl = this.escapeHtml(slide.background || '');
            const mediaUrl = this.escapeHtml(slide.imageCenter || '');
            const title = this.escapeHtml(slide.title || `Slide ${index + 1}`);
            const typeLabel = this.getTypeLabel(slide.type);

            item.innerHTML = `
                <div class="bg" style="background-image:url('${bgUrl}')"></div>

                ${mediaUrl ? `
                    <div class="thumb-media">
                        <img src="${mediaUrl}" alt="">
                    </div>
                ` : ''}

                <div class="thumb-overlay">
                    <div class="thumb-type">${typeLabel}</div>
                    <div class="thumb-title">${title}</div>
                </div>

                <div class="num">#${index + 1}</div>

                <div class="slide-tools">
                    <button type="button" class="slide-tool-btn" data-action="duplicate-slide" data-id="${slide.id}" title="Duplică">
                        ⧉
                    </button>
                    <button type="button" class="slide-tool-btn slide-tool-btn-delete" data-action="delete-slide" data-id="${slide.id}" title="Șterge">
                        ×
                    </button>
                </div>
            `;

            this.dom.slidesList.appendChild(item);
        });
    }
}