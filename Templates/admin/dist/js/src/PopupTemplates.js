import { STOCK_IMAGES, THEMES } from './constants.js';

export class PopupTemplates {
  static mediaLibrary(images = STOCK_IMAGES) {
    return {
      title: 'Alege o imagine',
      content: `
        <div class="media-library-grid">
          ${images.map((image) => `
            <button
              type="button"
              class="media-library-item"
              data-action="select-library-image"
              data-image-url="${image.url}"
            >
              <img src="${image.url}" alt="${image.name}">
              <span>${image.name}</span>
            </button>
          `).join('')}
        </div>

        <div class="popup-section" style="margin-top:16px;">
          <label class="label">Sau URL imagine</label>
          <input
            type="text"
            class="input"
            id="manual-image-url"
            placeholder="https://site.com/image.jpg"
          >
          <button
            type="button"
            class="btn btn-blue"
            data-action="apply-manual-image-url"
            style="margin-top:10px;width:100%;"
          >
            Aplica imaginea
          </button>
        </div>
      `,
      footerButtons: [
        {
          label: 'Inchide',
          className: 'btn btn-gray',
          close: true,
        }
      ]
    };
  }

  static mediaLibraryAdvanced(images = STOCK_IMAGES, audioLibrary = []) {
    return {
      title: 'Adauga media',
      content: `
        <div class="media-tabs">
          <button type="button" class="media-tab is-active" data-action="switch-media-tab" data-tab="images">Imagini</button>
          <button type="button" class="media-tab" data-action="switch-media-tab" data-tab="links">Linkuri</button>
          <button type="button" class="media-tab" data-action="switch-media-tab" data-tab="audio">Audio</button>
        </div>

        <div class="media-tab-panels">
          <div class="media-tab-panel is-active" data-panel="images">
            <div class="media-library-grid">
              ${images.map((image) => `
                <button
                  type="button"
                  class="media-library-item"
                  data-action="select-library-media"
                  data-media-type="image"
                  data-media-url="${image.url}"
                >
                  <img src="${image.url}" alt="${image.name}">
                  <span>${image.name}</span>
                </button>
              `).join('')}
            </div>
          </div>

          <div class="media-tab-panel" data-panel="links">
            <div class="popup-section">
              <label class="label">Tip media</label>
              <select class="input" id="manual-media-type">
                <option value="image">Imagine</option>
                <option value="gif">GIF</option>
                <option value="video">Video</option>
                <option value="youtube">YouTube</option>
              </select>

              <label class="label" style="margin-top:10px;">URL media</label>
              <input
                type="text"
                class="input"
                id="manual-media-url"
                placeholder="https://..."
              >

              <button
                type="button"
                class="btn btn-blue"
                data-action="apply-manual-media-url"
                style="margin-top:12px;width:100%;"
              >
                Aplica media
              </button>
            </div>
          </div>

          <div class="media-tab-panel" data-panel="audio">
            <div class="media-library-grid" style="margin-bottom:16px;">
              ${audioLibrary.map((audioItem) => `
                <button
                  type="button"
                  class="media-library-item"
                  data-action="select-library-media"
                  data-media-type="audio"
                  data-media-url="${audioItem.url}"
                >
                  <div style="padding:24px 12px;font-size:42px;">♪</div>
                  <span>${audioItem.name}</span>
                </button>
              `).join('')}
            </div>

            <div class="popup-section">
              <label class="label">URL audio</label>
              <input
                type="text"
                class="input"
                id="manual-audio-url"
                placeholder="https://site.com/audio.mp3"
              >

              <button
                type="button"
                class="btn btn-blue"
                data-action="apply-manual-audio-url"
                style="margin-top:12px;width:100%;"
              >
                Aplica audio
              </button>
            </div>

            <div class="popup-section" style="margin-top:16px;">
              <label class="label">Upload fisier audio / video</label>
              <input type="file" id="upload-media-file" class="input" accept="audio/*,video/*">
              <button
                type="button"
                class="btn btn-green"
                data-action="upload-media-file"
                style="margin-top:12px;width:100%;"
              >
                Incarca fisierul
              </button>
            </div>
          </div>
        </div>
      `,
      footerButtons: [
        {
          label: 'Inchide',
          className: 'btn btn-gray',
          close: true,
        }
      ]
    };
  }

  static themeLibrary(themes = THEMES) {
    return {
      title: 'Alege o tema',
      content: `
        <div class="media-library-grid">
          ${themes.map((theme) => `
            <button
              type="button"
              class="media-library-item"
              data-action="select-theme"
              data-theme-id="${theme.id}"
              data-theme-name="${theme.name}"
              data-theme-url="${theme.url}"
            >
              <img src="${theme.url}" alt="${theme.name}">
              <span>${theme.name}${theme.description ? ` - ${theme.description}` : ''}</span>
            </button>
          `).join('')}
        </div>
      `,
      footerButtons: [
        {
          label: 'Inchide',
          className: 'btn btn-gray',
          close: true,
        }
      ]
    };
  }

  static chooseSlideType() {
    return {
      title: 'Alege tipul de slide',
      content: `
        <div class="slide-type-grid">
          <button type="button" class="slide-type-card" data-action="create-slide-quiz">
            <div class="slide-type-icon">?</div>
            <div class="slide-type-title">Quiz / Grila</div>
            <div class="slide-type-text">
              Slide cu intrebare, imagine si raspunsuri.
            </div>
          </button>

          <button type="button" class="slide-type-card" data-action="create-slide-media">
            <div class="slide-type-icon">🖼</div>
            <div class="slide-type-title">Media / Informational</div>
            <div class="slide-type-text">
              Slide cu imagine, titlu, text si eventual buton/link.
            </div>
          </button>
        </div>
      `,
      footerButtons: [
        {
          label: 'Inchide',
          className: 'btn btn-gray',
          close: true,
        }
      ]
    };
  }

  static questionTypes(questionTypes = []) {
    const configuredQuestionTypes = Array.isArray(questionTypes) && questionTypes.length
      ? questionTypes
      : [
        { id: 'quiz', name: 'Quiz / Grila', desc: 'Intrebare cu raspunsuri si alegere corecta.', icon: '?' },
        { id: 'true-false', name: 'True / False', desc: 'Doua variante rapide.', icon: '✓' },
        { id: 'open-ended', name: 'Open ended', desc: 'Raspuns liber in text.', icon: '⌨' },
        { id: 'slider', name: 'Slider', desc: 'Selectare pe interval.', icon: '↔' },
        { id: 'puzzle', name: 'Puzzle', desc: 'Ordonare / jumble.', icon: '🧩' },
        { id: 'pin', name: 'Pin answer', desc: 'Pin pe imagine.', icon: '📍' },
        { id: 'media', name: 'Media / Informational', desc: 'Imagine, titlu, text, YouTube, buton.', icon: '🖼' },
      ];

    return {
      title: 'Alege tipul intrebarii',
      content: `
        <div class="slide-type-grid">
          ${configuredQuestionTypes.map((questionTypeItem) => `
            <button type="button" class="slide-type-card" data-action="select-question-type" data-type-id="${questionTypeItem.id}">
              <div class="slide-type-icon">${
                questionTypeItem.icon
                  ? (String(questionTypeItem.icon).startsWith('http')
                    ? `<img src="${questionTypeItem.icon}" alt="${questionTypeItem.name}" style="max-width:56px;max-height:56px;">`
                    : questionTypeItem.icon)
                  : '•'
              }</div>
              <div class="slide-type-title">${questionTypeItem.name}</div>
              <div class="slide-type-text">${questionTypeItem.desc || ''}</div>
            </button>
          `).join('')}
        </div>
      `,
      footerButtons: [
        {
          label: 'Inchide',
          className: 'btn btn-gray',
          close: true,
        }
      ]
    };
  }
}
