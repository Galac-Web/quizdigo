/**
 * QuizDigo Builder — CLEAN + FULL (UNIFIED, NO DUPLICATES)
 * - Single popup modal (theme/type/media) + correct context
 * - Event delegation + DOM cache
 * - Controlled rendering
 * - decodeEntities + normalizeUrl
 * - Prevent default + type="button" safe
 * - applyMedia -> renderCanvas
 * - Jumble DnD moves answers + answerImages
 * - Timer circle dasharray fix
 * - Music global (upload/stock/url) + preview audio
 * - New types: slider + pin
 * - Pin: click image add pin, click pin toggle correct, SHIFT+click delete, clear pins
 * - Jumble desktop layout: image left, list right; mobile normal
 */

const qs = (s, r = document) => r.querySelector(s);

/* -------------------------
   DOM CACHE
-------------------------- */
const dom = {
    slidesList: qs("#slides-list"),
    card: qs("#card-canvas"),
    qTitle: qs("#question-title"),
    mediaCenter: qs("#media-center"),
    mediaCenterInner: qs("#media-center-inner"),
    answers: qs("#answers"),
    themePreview: qs("#theme-preview"),
    themeName: qs("#theme-name"),
    typeIcon: qs("#type-icon"),
    typeName: qs("#type-name"),

    popup: qs("#popup"),
    popupTitle: qs("#popup-title"),
    popupBody: qs("#popup-body"),

    quizSettings: qs("#quiz-settings"),
    quizTitle: qs("#quiz-title"),
    quizDesc: qs("#quiz-desc"),
    quizVisibility: qs("#quiz-visibility"),
    quizLang: qs("#quiz-lang"),
    cover: qs("#cover"),
    coverHint: qs("#cover-hint"),

    bonusToggle: qs("#bonus-toggle"),
    bonusRange: qs("#bonus-range"),
    bonusStatus: qs("#bonus-status"),
    bonusTimeLabel: qs("#bonus-time-label"),

    preview: qs("#preview"),
    previewStage: qs("#preview-stage"),
    previewCounter: qs("#preview-counter"),
    previewTitle: qs("#preview-title"),
    previewMedia: qs("#preview-media"),
    previewAnswers: qs("#preview-answers"),
    timerNum: qs("#timer-num"),
    timerBar: qs("#timer-bar"),

    // UI muzică (buton din settings)
    musicIcon: qs("#music-icon"),
    musicName: qs("#music-name"),

    // per-slide multiple answers
    selectMultiple: qs("#select-multiple"),
};

/* -------------------------
   DATA
-------------------------- */
const STOCK_IMAGES = [
    "https://images.unsplash.com/photo-1503676260728-1c00da094a0b?q=80&w=800",
    "https://images.unsplash.com/photo-1518770660439-4636190af475?q=80&w=800",
    "https://images.unsplash.com/photo-1434031215662-72ee337ec3b3?q=80&w=800",
    "https://images.unsplash.com/photo-1557683316-973673baf926?q=80&w=800",
];

const THEMES = [
    { name: "Abstract Blue", url: "https://images.unsplash.com/photo-1557683316-973673baf926?q=80&w=1200" },
    { name: "Educație", url: "https://images.unsplash.com/photo-1503676260728-1c00da094a0b?q=80&w=1200" },
    { name: "Tehnologie", url: "https://images.unsplash.com/photo-1518770660439-4636190af475?q=80&w=1200" },
];

// include slider + pin direct (fără patch-uri)
const Q_TYPES = [
    { id: "quiz",       name: "Quiz (Grilă)",     icon: "https://quizdigo.com/quizigo/11.png", desc: "Alegere multiplă (4)" },
    { id: "true-false", name: "Adevărat / Fals",  icon: "https://quizdigo.com/quizigo/22.png", desc: "Două variante" },
    { id: "open-ended", name: "Type Answer",      icon: "https://quizdigo.com/quizigo/33.png", desc: "Tastează răspunsul" },
    { id: "jumble",     name: "Puzzle",           icon: "https://quizdigo.com/quizigo/44.png", desc: "Ordonare" },
    { id: "slider",     name: "Slider",           icon: "https://quizdigo.com/quizigo/55.png", desc: "Interval + valoare" },
    { id: "pin",        name: "Pin answer",       icon: "https://quizdigo.com/quizigo/66.png", desc: "Pin pe imagine" },
];

const COLORS = ["purple", "orange", "green", "yellow"];

// stock audio (înlocuiește cu linkuri reale)
const STOCK_AUDIO = [
    { name: "LoFi 1", url: "https://site-ul-tau.com/audio/lofi1.mp3" },
    { name: "Calm 2", url: "https://site-ul-tau.com/audio/calm2.mp3" },
];

let quizData = {
    settings: {
        theme: "standart",
        themeUrl: THEMES[0].url,
        timeLimit: "10s",
        bonusSpeed: true,
        bonusTime: 5,
        title: "",
        description: "",
        visibility: "private",
        lang: "ro",
        coverImage: "",
        // muzică globală
        musicUrl: "",

        // optional feedback sounds (correct / wrong)
        correctSound: "",
        wrongSound: "",
    },
    slides: [],
    currentSlideId: null,
    id_quiz: null,
};

/* -------------------------
   SAFETY HELPERS
-------------------------- */
function has(el) { return !!el; }

function decodeEntities(str) {
    const t = document.createElement("textarea");
    t.innerHTML = String(str ?? "");
    return t.value;
}
function normalizeUrl(url) {
    url = decodeEntities(String(url || "").trim());
    return url.replace(/\s+/g, "");
}
function escapeHtml(str) {
    return String(str ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

function timeToSeconds(v) {
    v = String(v || "").trim();
    if (v.endsWith("s")) return parseInt(v, 10) || 20;
    if (v.endsWith("m")) return (parseInt(v, 10) || 1) * 60;
    const n = parseInt(v, 10);
    return Number.isFinite(n) ? n : 20;
}

/* -------------------------
   SLIDE SHAPE (slider/pin)
-------------------------- */
function ensureSlideShape(slide) {
    if (!slide) return;

    if (slide.type === "slider") {
        if (!slide.slider) slide.slider = { min: 0, max: 50, correct: 25 };
        if (typeof slide.slider.min !== "number") slide.slider.min = 0;
        if (typeof slide.slider.max !== "number") slide.slider.max = 50;
        if (typeof slide.slider.correct !== "number") slide.slider.correct = Math.round((slide.slider.min + slide.slider.max) / 2);

        // clamp
        if (slide.slider.max < slide.slider.min) slide.slider.max = slide.slider.min;
        if (slide.slider.correct < slide.slider.min) slide.slider.correct = slide.slider.min;
        if (slide.slider.correct > slide.slider.max) slide.slider.correct = slide.slider.max;
    }

    if (slide.type === "pin") {
        if (!Array.isArray(slide.pins)) slide.pins = [];
        if (!slide.imageCenter) slide.imageCenter = "";
    }

    // selection mode (quiz only)
    if (!slide.selectType) slide.selectType = "single"; // single | multiple
    if (slide.selectType !== "single" && slide.selectType !== "multiple") slide.selectType = "single";
    if (!Array.isArray(slide.correctAnswerIndexes)) {
        slide.correctAnswerIndexes = (Number.isFinite(slide.correctAnswerIndex) ? [slide.correctAnswerIndex] : []);
    }
}

/* -------------------------
   CREATE / GET SLIDE
-------------------------- */
function newSlide() {
    return {
        id: Date.now() + Math.floor(Math.random() * 1000),
        type: "quiz",
        title: "",
        background: quizData.settings.themeUrl,
        imageCenter: "",
        correctAnswerIndex: null,
        answers: ["", "", "", ""],
        answerImages: ["", "", "", ""],
        // existent la tine
        musicUrl: "",
        // slider/pin optional
        slider: null,
        pins: null,

        // quiz selection mode
        selectType: "single",
        correctAnswerIndexes: [],
    };
}
function ensureInit() {
    if (!quizData.slides.length) {
        quizData.slides.push(newSlide());
        quizData.currentSlideId = quizData.slides[0].id;
    }
}
function getSlide() {
    return quizData.slides.find(s => s.id === quizData.currentSlideId) || null;
}

/* -------------------------
   LAYOUT HELPERS (jumble)
-------------------------- */
const JUMBLE_LAYOUT = (function(){
    const DEFAULTS = new WeakMap();
    const PAD = 22;

    function stash(el){
        if (!el || DEFAULTS.has(el)) return;
        DEFAULTS.set(el, {
            position: el.style.position || "",
            left: el.style.left || "",
            right: el.style.right || "",
            top: el.style.top || "",
            bottom: el.style.bottom || "",
            width: el.style.width || "",
            height: el.style.height || "",
            aspectRatio: el.style.aspectRatio || "",
            transform: el.style.transform || "",
            marginLeft: el.style.marginLeft || "",
            marginTop: el.style.marginTop || "",
            display: el.style.display || "",
            gridTemplateColumns: el.style.gridTemplateColumns || "",
            gap: el.style.gap || "",
        });
    }

    function restore(el){
        if (!el || !DEFAULTS.has(el)) return;
        Object.assign(el.style, DEFAULTS.get(el));
    }

    function isDesktop(){
        return !window.matchMedia("(max-width: 900px)").matches;
    }

    function getTopOffset(card){
        try{
            const titleBox = card.querySelector(".title-box");
            if (!titleBox) return 92;
            const r = titleBox.getBoundingClientRect();
            const cr = card.getBoundingClientRect();
            const top = (r.bottom - cr.top) + 14;
            return Math.max(80, Math.round(top));
        }catch{
            return 92;
        }
    }

    function apply(slide){
        const card = dom.card, media = dom.mediaCenter, ans = dom.answers;
        if (!slide || !card || !media || !ans) return;

        if (slide.type !== "jumble" || !isDesktop()){
            restore(media); restore(ans);
            return;
        }

        const cs = getComputedStyle(card);
        if (cs.position === "static") card.style.position = "relative";

        stash(media); stash(ans);

        const top = getTopOffset(card);
        const mediaW = "clamp(260px, 34%, 420px)";
        const mediaH = "clamp(220px, 34vw, 420px)";

        Object.assign(media.style, {
            position: "absolute",
            left: PAD + "px",
            top: top + "px",
            right: "",
            bottom: "",
            width: mediaW,
            height: mediaH,
            aspectRatio: "auto",
            transform: "",
            marginLeft: "",
            marginTop: "",
        });

        Object.assign(ans.style, {
            position: "absolute",
            left: "",
            right: PAD + "px",
            top: top + "px",
            bottom: "",
            width: `calc(100% - (${PAD}px * 2) - ${mediaW} - 18px)`,
            transform: "",
            marginLeft: "",
            marginTop: "",
        });

        ans.style.gridTemplateColumns = "1fr";
        ans.style.gap = ans.style.gap || "14px";
    }

    return { apply, restore };
})();

/* -------------------------
   PIN DOTS (builder)
-------------------------- */
function cleanupPinDotsBuilder() {
    if (!has(dom.mediaCenter)) return;
    dom.mediaCenter.querySelectorAll(".pin-dot").forEach(n => n.remove());
}
function renderPinDotsBuilder(slide) {
    if (!has(dom.mediaCenter)) return;
    cleanupPinDotsBuilder();
    if (!slide?.imageCenter) return;

    (slide.pins || []).forEach((p, idx) => {
        const dot = document.createElement("div");
        dot.className = "pin-dot";
        dot.dataset.action = "pin-dot";
        dot.dataset.idx = String(idx);
        dot.style.left = (p.x * 100) + "%";
        dot.style.top = (p.y * 100) + "%";
        dot.title = p.correct ? "Corect (SHIFT+click = șterge)" : "Pin (SHIFT+click = șterge)";
        dot.textContent = p.label ? String(p.label).slice(0, 2) : String(idx + 1);

        if (p.correct) {
            dot.style.borderColor = "rgba(46,204,113,.95)";
            dot.style.background = "rgba(46,204,113,.92)";
            dot.style.boxShadow = "0 0 0 6px rgba(46,204,113,.18), 0 10px 18px rgba(0,0,0,.18)";
        }
        dom.mediaCenter.appendChild(dot);
    });
}
function pxToPercentInEl(el, clientX, clientY) {
    const r = el.getBoundingClientRect();
    const x = (clientX - r.left) / r.width;
    const y = (clientY - r.top) / r.height;
    return { x: Math.max(0, Math.min(1, x)), y: Math.max(0, Math.min(1, y)) };
}

/* -------------------------
   RENDER: Slides
-------------------------- */
function renderSlides() {
    if (!has(dom.slidesList)) return;
    dom.slidesList.innerHTML = "";

    quizData.slides.forEach((s, i) => {
        const el = document.createElement("div");
        el.className = `slide ${s.id === quizData.currentSlideId ? "active" : ""}`;
        el.dataset.action = "select-slide";
        el.dataset.id = String(s.id);

        el.innerHTML = `
      <div class="bg" style="background-image:url('${escapeHtml(s.background || "")}')"></div>
      <div class="num">#${i + 1}</div>
      <button type="button" class="del" data-action="delete-slide" data-id="${s.id}" title="Delete">&times;</button>
    `;
        dom.slidesList.appendChild(el);
    });
}

/* -------------------------
   RENDER: Canvas
-------------------------- */
function renderCanvas() {
    const slide = getSlide();
    if (!slide) return;

    ensureSlideShape(slide);

    // background
    if (has(dom.card)) dom.card.style.backgroundImage = `url('${slide.background || ""}')`;

    // title
    if (has(dom.qTitle)) dom.qTitle.value = slide.title || "";

    // center media
    if (has(dom.mediaCenter) && has(dom.mediaCenterInner)) {
        // mediaCenter should be relative for pin dots
        const cs = getComputedStyle(dom.mediaCenter);
        if (cs.position === "static") dom.mediaCenter.style.position = "relative";

        if (slide.imageCenter) {
            dom.mediaCenter.style.backgroundImage = `url('${slide.imageCenter}')`;
            dom.mediaCenter.style.backgroundSize = "cover";
            dom.mediaCenter.style.backgroundPosition = "center";
            dom.mediaCenterInner.style.display = "none";
        } else {
            dom.mediaCenter.style.backgroundImage = "none";
            dom.mediaCenterInner.style.display = "block";
        }
    }

    // type info
    const t = Q_TYPES.find(x => x.id === slide.type) || Q_TYPES[0];
    if (has(dom.typeName)) dom.typeName.textContent = t.name;
    if (has(dom.typeIcon)) dom.typeIcon.src = t.icon;

    // theme preview
    if (has(dom.themePreview)) {
        dom.themePreview.style.backgroundImage = `url('${quizData.settings.themeUrl}')`;
        dom.themePreview.style.backgroundSize = "cover";
        dom.themePreview.style.backgroundPosition = "center";
    }
    if (has(dom.themeName)) dom.themeName.textContent = quizData.settings.theme || "standart";

    // muzică UI
    if (has(dom.musicName)) dom.musicName.textContent = quizData.settings.musicUrl ? "Selectat" : "Fără muzică";
    if (has(dom.musicIcon)) dom.musicIcon.src = quizData.settings.musicUrl
        ? "https://quizdigo.com/Musical Note.png"
        : "https://quizdigo.com/Musical Note.png";

    // answers
    renderAnswers(slide);

    // per-slide multiple switch reflect (Quiz only)
    if (has(dom.selectMultiple)) {
        const isQuizType = (slide.type === "quiz");
        dom.selectMultiple.disabled = !isQuizType;
        dom.selectMultiple.checked = (isQuizType && slide.selectType === "multiple");
        dom.selectMultiple.parentElement?.classList.toggle("is-disabled", !isQuizType);
    }

    // pin dots builder only for pin type
    if (slide.type === "pin") renderPinDotsBuilder(slide);
    else cleanupPinDotsBuilder();

    // jumble layout apply
    JUMBLE_LAYOUT.apply(slide);
}

function renderAnswers(slide) {
    if (!has(dom.answers)) return;
    ensureSlideShape(slide);

    dom.answers.innerHTML = "";

    // OPEN ENDED
    if (slide.type === "open-ended") {
        dom.answers.className = "answers a1";
        const wrap = document.createElement("div");
        wrap.className = "answer purple";
        wrap.innerHTML = `
      <div class="answer-left">
        <input class="answer-input" data-action="answer-input" data-idx="0"
          placeholder="Tastează răspunsul corect..."
          value="${escapeHtml(slide.answers?.[0] || "")}">
      </div>
      <div class="correctbtn" data-action="set-correct" data-idx="0" title="Corect"></div>
    `;
        dom.answers.appendChild(wrap);
        if (slide.correctAnswerIndex !== 0) slide.correctAnswerIndex = 0;
        return;
    }

    // TRUE / FALSE
    if (slide.type === "true-false") {
        dom.answers.className = "answers a2";
        const labels = ["True", "False"];
        slide.answers = labels;
        slide.answerImages = ["", ""];

        labels.forEach((label, i) => {
            const el = document.createElement("div");
            el.className = `answer ${i === 0 ? "purple" : "orange"} ${slide.correctAnswerIndex === i ? "correct" : ""}`;
            el.innerHTML = `
        <div class="answer-left">
          <div style="font-weight:950;font-size:20px">${label}</div>
        </div>
        <div class="correctbtn" data-action="set-correct" data-idx="${i}"></div>
      `;
            dom.answers.appendChild(el);
        });
        return;
    }

    // SLIDER
    if (slide.type === "slider") {
        dom.answers.className = "answers a1";
        const minV = Number(slide.slider?.min ?? 0);
        const maxV = Number(slide.slider?.max ?? 50);
        let corV = Number(slide.slider?.correct ?? Math.round((minV + maxV) / 2));
        if (corV < minV) corV = minV;
        if (corV > maxV) corV = maxV;
        slide.slider.correct = corV;

        const wrap = document.createElement("div");
        wrap.style.width = "100%";
        wrap.style.display = "grid";
        wrap.style.gap = "12px";

        wrap.innerHTML = `
      <div style="display:flex; gap:10px; width:100%; align-items:center; justify-content:center; background:rgba(255,255,255,.92); padding:14px; border-radius:14px; box-shadow:0 10px 22px rgba(0,0,0,.18);">
        <div style="width:90px">
          <div class="label" style="margin:0 0 6px">Min</div>
          <input class="input" data-action="slider-min" type="number" value="${minV}">
        </div>

        <div style="flex:1; min-width:160px">
          <div class="label" style="margin:0 0 6px">Corect</div>
          <input data-action="slider-correct" type="range" min="${minV}" max="${maxV}" value="${corV}" style="width:100%">
          <div data-role="slider-val" style="text-align:center; font-weight:950; margin-top:6px">${corV}</div>
        </div>

        <div style="width:90px">
          <div class="label" style="margin:0 0 6px">Max</div>
          <input class="input" data-action="slider-max" type="number" value="${maxV}">
        </div>
      </div>
      <div class="hint" style="background:rgba(255,255,255,.88); padding:10px 12px; border-radius:12px;">
        Setează intervalul și valoarea corectă. În preview, user-ul alege o valoare pe slider.
      </div>
    `;
        dom.answers.appendChild(wrap);
        return;
    }

    // PIN
    if (slide.type === "pin") {
        dom.answers.className = "answers a1";

        const hint = document.createElement("div");
        hint.style.width = "min(900px,95%)";
        hint.style.background = "rgba(255,255,255,.92)";
        hint.style.borderRadius = "14px";
        hint.style.padding = "12px 14px";
        hint.style.boxShadow = "0 10px 22px rgba(0,0,0,.18)";
        hint.innerHTML = `
      <div style="font-weight:950; margin-bottom:6px">Pin answer</div>
      <div style="font-weight:800; color:#334155; font-size:13px; line-height:1.35">
        Click pe imagine (media-center) ca să adaugi pin. Click pe pin = marchezi corect. SHIFT+click = ștergi.
      </div>
      <div style="margin-top:10px; display:flex; gap:10px; flex-wrap:wrap">
        <button type="button" class="btn btn-blue" data-action="open-media" data-media="center">📷 Alege imagine</button>
        <button type="button" class="btn btn-gray" data-action="pin-clear">🧹 Șterge pini</button>
      </div>
    `;
        dom.answers.appendChild(hint);
        return;
    }

    // quiz / jumble
    if (!slide.answers || slide.answers.length !== 4) slide.answers = ["", "", "", ""];
    if (!slide.answerImages || slide.answerImages.length !== 4) slide.answerImages = ["", "", "", ""];

    // JUMBLE
    if (slide.type === "jumble") {
        dom.answers.className = "answers a1";
        slide.correctAnswerIndex = null;

        slide.answers.forEach((ans, i) => {
            const el = document.createElement("div");
            el.className = `answer ${COLORS[i]}`;
            el.draggable = true;
            el.dataset.drag = "jumble";
            el.dataset.idx = String(i);

            el.innerHTML = `
        <div class="answer-left">
          <div style="font-weight:950">${i + 1}.</div>
          <input class="answer-input" data-action="answer-input" data-idx="${i}"
            placeholder="Pasul ${i + 1}" value="${escapeHtml(ans)}">
        </div>
        <div style="font-size:20px;font-weight:900;opacity:.85;cursor:grab">☰</div>
      `;
            dom.answers.appendChild(el);
        });
        return;
    }

    // QUIZ (4)
    dom.answers.className = "answers a2";
    slide.answers.forEach((ans, i) => {
        const img = slide.answerImages[i];
        const el = document.createElement("div");

        const isCorrect = (slide.selectType === "multiple")
            ? (Array.isArray(slide.correctAnswerIndexes) && slide.correctAnswerIndexes.includes(i))
            : (slide.correctAnswerIndex === i);

        el.className = `answer ${COLORS[i]} ${isCorrect ? "correct" : ""}`;

        const imgHtml = img
            ? `<div class="imgbox">
          <img src="${escapeHtml(img)}" alt="">
          <button type="button" data-action="remove-answer-img" data-idx="${i}">×</button>
        </div>`
            : "";

        el.innerHTML = `
      <div class="answer-left">
        <input class="answer-input" data-action="answer-input" data-idx="${i}"
          placeholder="Adaugă răspunsul ${i + 1}" value="${escapeHtml(ans)}">
        ${imgHtml}
        <button type="button" class="iconbtn" data-action="open-media" data-media="answer" data-idx="${i}" title="Add image">🖼</button>
      </div>
      <div class="correctbtn" data-action="set-correct" data-idx="${i}" title="Corect"></div>
    `;
        dom.answers.appendChild(el);
    });
}

/* -------------------------
   POPUP (theme/type/media)
-------------------------- */
let popupContext = { kind: null, mediaTarget: null, mediaIdx: null };

function openPopup(kind) {
    if (!has(dom.popup) || !has(dom.popupTitle) || !has(dom.popupBody)) return;

    // MEDIA POPUP: keep context
    if (kind === "media") {
        const isMusic = (popupContext.mediaTarget === "music");
        dom.popupTitle.textContent = isMusic ? "Muzică (Upload / URL)" : "Media (Upload / URL)";

        dom.popupBody.innerHTML = `
      <div class="mediaActions">
        <button type="button" class="btn btn-blue" data-action="media-upload">
          ${isMusic ? "🎵 Upload audio" : "📁 Upload"}
        </button>
      </div>

      ${isMusic ? `
        <div class="gridThemes">
          ${STOCK_AUDIO.map(a => `
            <button type="button" class="tile" data-action="media-pick" data-url="${a.url}">
              <div style="padding:10px;font-weight:900">${a.name}</div>
              <audio controls style="width:100%" src="${a.url}"></audio>
              <span>Audio</span>
            </button>
          `).join("")}
        </div>
      ` : `
        <div class="gridThemes">
          ${STOCK_IMAGES.map(u => `
            <button type="button" class="tile" data-action="media-pick" data-url="${u}">
              <img src="${u}" alt="">
              <span>Stock</span>
            </button>
          `).join("")}
        </div>
      `}

      <div style="margin-top:12px">
        <label class="label">Sau URL extern</label>
        <input class="input" id="media-url" placeholder="${isMusic ? "https://site.com/audio.mp3" : "https://site.com/img.jpg"}" />
        <button type="button" class="btn btn-green" style="margin-top:10px;width:100%" data-action="media-url-ok">OK</button>
      </div>
    `;

        dom.popup.setAttribute("aria-hidden", "false");
        return;
    }

    dom.popup.setAttribute("aria-hidden", "false");

    if (kind === "theme") {
        dom.popupTitle.textContent = "Alege o temă";
        dom.popupBody.innerHTML = `
      <div class="gridThemes">
        ${THEMES.map(t => `
          <button type="button" class="tile" data-action="set-theme" data-url="${t.url}" data-name="${t.name}">
            <img src="${t.url}" alt="">
            <span>${t.name}</span>
          </button>
        `).join("")}
      </div>
    `;
        return;
    }

    if (kind === "type") {
        dom.popupTitle.textContent = "Tipuri de întrebări";
        dom.popupBody.innerHTML = `
      <div class="gridTypes">
        ${Q_TYPES.map(t => `
          <button type="button" class="typeTile" data-action="set-type" data-type="${t.id}">
            <img src="${t.icon}" alt="">
            <div>
              <strong>${t.name}</strong>
              <small>${t.desc}</small>
            </div>
          </button>
        `).join("")}
      </div>
    `;
        return;
    }
}

function closePopup() {
    if (!has(dom.popup)) return;
    dom.popup.setAttribute("aria-hidden", "true");
    popupContext = { kind: null, mediaTarget: null, mediaIdx: null };
}

function openMedia(target, idx = null) {
    popupContext = { kind: "media", mediaTarget: target, mediaIdx: idx };
    openPopup("media");
}
function openMusic() {
    popupContext = { kind: "media", mediaTarget: "music", mediaIdx: null };
    openPopup("media");
}

function applyMedia(url) {
    url = normalizeUrl(url);
    if (!url) return;

    const slide = getSlide();
    if (!slide) return;

    if (popupContext.mediaTarget === "center") {
        slide.imageCenter = url;
        renderCanvas();
        closePopup();
        return;
    }

    if (popupContext.mediaTarget === "cover") {
        quizData.settings.coverImage = url;

        if (has(dom.cover)) {
            dom.cover.style.backgroundImage = `url('${url}')`;
            dom.cover.style.backgroundSize = "cover";
            dom.cover.style.backgroundPosition = "center";
        }
        if (has(dom.coverHint)) dom.coverHint.style.display = "none";

        closePopup();
        return;
    }

    if (popupContext.mediaTarget === "music") {
        quizData.settings.musicUrl = url;

        if (has(dom.musicName)) dom.musicName.textContent = "Selectat";
        if (has(dom.musicIcon)) dom.musicIcon.src = "https://quizdigo.com/quizigo/music-on.png";

        closePopup();
        return;
    }

    if (popupContext.mediaTarget === "answer") {
        const i = Number(popupContext.mediaIdx);
        if (!Number.isFinite(i)) return;

        if (!slide.answerImages || slide.answerImages.length !== 4) slide.answerImages = ["", "", "", ""];
        if (!slide.answers || slide.answers.length !== 4) slide.answers = ["", "", "", ""];

        slide.answerImages[i] = url;
        slide.answers[i] = "";
        renderCanvas();
        closePopup();
    }
}

/* -------------------------
   QUIZ SETTINGS MODAL
-------------------------- */
function openQuizSettings() {
    if (!has(dom.quizSettings)) return;

    if (has(dom.quizTitle)) dom.quizTitle.value = quizData.settings.title || "";
    if (has(dom.quizDesc)) dom.quizDesc.value = quizData.settings.description || "";
    if (has(dom.quizVisibility)) dom.quizVisibility.value = quizData.settings.visibility || "private";
    if (has(dom.quizLang)) dom.quizLang.value = quizData.settings.lang || "ro";

    if (quizData.settings.coverImage && has(dom.cover) && has(dom.coverHint)) {
        dom.cover.style.backgroundImage = `url('${quizData.settings.coverImage}')`;
        dom.cover.style.backgroundSize = "cover";
        dom.cover.style.backgroundPosition = "center";
        dom.coverHint.style.display = "none";
    } else {
        if (has(dom.cover)) dom.cover.style.backgroundImage = "none";
        if (has(dom.coverHint)) dom.coverHint.style.display = "block";
    }

    dom.quizSettings.setAttribute("aria-hidden", "false");
}
function closeQuizSettings() {
    if (!has(dom.quizSettings)) return;
    dom.quizSettings.setAttribute("aria-hidden", "true");
}
function saveQuizSettings() {
    const title = String(dom.quizTitle?.value || "").trim();
    if (!title) {
        if (has(dom.quizTitle)) dom.quizTitle.style.borderColor = "red";
        alert("Introdu un titlu pentru quiz!");
        return;
    }
    if (has(dom.quizTitle)) dom.quizTitle.style.borderColor = "";

    quizData.settings.title = title;
    quizData.settings.description = dom.quizDesc?.value || "";
    quizData.settings.visibility = dom.quizVisibility?.value || "private";
    quizData.settings.lang = dom.quizLang?.value || "ro";
    closeQuizSettings();
}

/* -------------------------
   SLIDE LOGIC
-------------------------- */
function addSlide() {
    quizData.slides.push(newSlide());
    quizData.currentSlideId = quizData.slides[quizData.slides.length - 1].id;
    renderSlides();
    renderCanvas();
}
function selectSlide(id) {
    quizData.currentSlideId = id;
    renderSlides();
    renderCanvas();
}
function deleteSlide(id) {
    if (quizData.slides.length === 1) return;
    quizData.slides = quizData.slides.filter(s => s.id !== id);
    quizData.currentSlideId = quizData.slides[0].id;
    renderSlides();
    renderCanvas();
}
function setTheme(url, name) {
    quizData.settings.themeUrl = url;
    quizData.settings.theme = name;

    const slide = getSlide();
    if (slide) slide.background = url;

    renderSlides();
    renderCanvas();
    closePopup();
}

function setType(typeId) {
    const slide = getSlide();
    if (!slide) return;

    slide.type = typeId;
    ensureSlideShape(slide);

    if (typeId === "true-false") {
        slide.answers = ["True", "False"];
        slide.answerImages = ["", ""];
        slide.correctAnswerIndex = null;
    } else if (typeId === "open-ended") {
        slide.answers = [slide.answers?.[0] || ""];
        slide.answerImages = [""];
        slide.correctAnswerIndex = 0;
    } else if (typeId === "jumble") {
        slide.answers = ["", "", "", ""];
        slide.answerImages = ["", "", "", ""];
        slide.correctAnswerIndex = null;
    } else if (typeId === "slider") {
        slide.answers = ["", "", "", ""];
        slide.answerImages = ["", "", "", ""];
        slide.correctAnswerIndex = null;
        slide.slider = slide.slider || { min: 0, max: 50, correct: 25 };
    } else if (typeId === "pin") {
        slide.answers = ["", "", "", ""];
        slide.answerImages = ["", "", "", ""];
        slide.correctAnswerIndex = null;
        slide.pins = Array.isArray(slide.pins) ? slide.pins : [];
        // pin needs imageCenter; keep whatever exists
    } else {
        // quiz
        slide.answers = ["", "", "", ""];
        slide.answerImages = ["", "", "", ""];
        slide.correctAnswerIndex = null;
    }

    // reset multi-correct when switching away / to quiz
    if (typeId !== "quiz") {
        slide.selectType = "single";
        slide.correctAnswerIndexes = [];
    } else {
        slide.selectType = slide.selectType || "single";
        slide.correctAnswerIndexes = Array.isArray(slide.correctAnswerIndexes) ? slide.correctAnswerIndexes : [];
    }

    renderCanvas();
    closePopup();
}

function setCorrect(idx) {
    const slide = getSlide();
    if (!slide) return;

    if (slide.type === "open-ended") {
        slide.correctAnswerIndex = 0;
    } else {
        if (slide.type === "quiz" && slide.selectType === "multiple") {
            if (!Array.isArray(slide.correctAnswerIndexes)) slide.correctAnswerIndexes = [];
            if (slide.correctAnswerIndexes.includes(idx)) {
                slide.correctAnswerIndexes = slide.correctAnswerIndexes.filter(i => i !== idx);
            } else {
                slide.correctAnswerIndexes.push(idx);
            }
            // keep legacy field as first correct (optional)
            slide.correctAnswerIndex = slide.correctAnswerIndexes.length ? slide.correctAnswerIndexes[0] : null;
        } else {
            slide.correctAnswerIndex = (slide.correctAnswerIndex === idx) ? null : idx;
            slide.correctAnswerIndexes = Number.isFinite(slide.correctAnswerIndex) ? [slide.correctAnswerIndex] : [];
        }
    }
    renderAnswers(slide);
}

function removeAnswerImg(idx) {
    const slide = getSlide();
    if (!slide?.answerImages) return;
    slide.answerImages[idx] = "";
    renderAnswers(slide);
}

/* -------------------------
   SAVE / LOAD (Local + API)
-------------------------- */
function saveLocal() {
    localStorage.setItem("quizdigo_project", JSON.stringify(quizData));
}
function loadLocal() {
    const raw = localStorage.getItem("quizdigo_project");
    if (!raw) return false;
    try {
        const data = JSON.parse(raw);
        if (data && data.slides && data.slides.length) {
            quizData = data;
            if (!quizData.currentSlideId) quizData.currentSlideId = quizData.slides[0].id;
            return true;
        }
    } catch {}
    return false;
}

async function saveRemote() {
    const endpoint = "/public/crudaddquizz";
    const r = await fetch(endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(quizData),
    });
    const j = await r.json().catch(() => null);
    if (j?.success && j?.id_quiz) quizData.id_quiz = j.id_quiz;
    return j;
}
async function loadRemote() {
    if (!quizData.id_quiz) return false;

    const endpoint = "/public/getaddquizz?id=" + encodeURIComponent(quizData.id_quiz);
    const r = await fetch(endpoint, {
        credentials: "same-origin"
    });

    const j = await r.json().catch(() => null);

    if (j && j.success && j.data && Array.isArray(j.data.slides) && j.data.slides.length) {
        quizData = j.data;
        if (!quizData.currentSlideId) {
            quizData.currentSlideId = quizData.slides[0].id;
        }
        return true;
    }

    return false;
}

/* -------------------------
   PREVIEW
-------------------------- */
let pIndex = 0;
let pTimer = null;
let pLeft = 0;

// scoring / selection (preview)
let pSelected = [];
let pStartTime = 0;
let pScore = 0;
let pLastPinHitCorrect = false;
let pLastSliderValue = null;

function openPreview() {
    if (!has(dom.preview)) return;
    dom.preview.setAttribute("aria-hidden", "false");
    pIndex = 0;
    pScore = 0;
    startPreviewSlide();
}
function closePreview() {
    if (pTimer) clearInterval(pTimer);
    pTimer = null;
    if (has(dom.preview)) dom.preview.setAttribute("aria-hidden", "true");

    const a = qs("#preview-audio");
    if (a) { try { a.pause(); } catch {} }
}

function startPreviewSlide() {
    if (pTimer) clearInterval(pTimer);
    pTimer = null;

    const slide = quizData.slides[pIndex];
    if (!slide) return;

    ensureSlideShape(slide);

    pStartTime = Date.now();
    pSelected = [];
    pLastPinHitCorrect = false;
    pLastSliderValue = null;

    if (has(dom.previewStage)) {
        dom.previewStage.style.backgroundImage = `url('${slide.background || ""}')`;
        dom.previewStage.style.backgroundSize = "cover";
        dom.previewStage.style.backgroundPosition = "center";
    }

    if (has(dom.previewCounter)) dom.previewCounter.textContent = `${pIndex + 1}/${quizData.slides.length}`;
    if (has(dom.previewTitle)) dom.previewTitle.textContent = slide.title || "Fără titlu";

    if (has(dom.previewMedia)) {
        if (slide.imageCenter) {
            dom.previewMedia.style.display = "block";
            dom.previewMedia.style.backgroundImage = `url('${slide.imageCenter}')`;
            dom.previewMedia.style.backgroundSize = "cover";
            dom.previewMedia.style.backgroundPosition = "center";
        } else {
            dom.previewMedia.style.display = "none";
            dom.previewMedia.style.backgroundImage = "none";
        }
    }

    renderPreviewAnswers(slide);

    const total = timeToSeconds(quizData.settings.timeLimit);
    pLeft = total;

    // timer circle
    if (has(dom.timerBar)) {
        dom.timerBar.style.strokeDasharray = dom.timerBar.style.strokeDasharray || "283";
        dom.timerBar.style.strokeDashoffset = "0";
        dom.timerBar.style.stroke = "#fff";
    }
    if (has(dom.timerNum)) dom.timerNum.textContent = String(pLeft);

    // preview audio
    const a = qs("#preview-audio");
    if (a) {
        const mu = quizData.settings.musicUrl || "";
        if (mu) {
            if (a.src !== mu) a.src = mu;
            a.volume = 0.5;
            a.play().catch(() => {});
        } else {
            try { a.pause(); } catch {}
            a.removeAttribute("src");
            a.load?.();
        }
    }

    pTimer = setInterval(() => {
        pLeft--;
        if (has(dom.timerNum)) dom.timerNum.textContent = String(pLeft);

        if (has(dom.timerBar)) {
            const offset = 283 - (pLeft / total) * 283;
            dom.timerBar.style.strokeDashoffset = String(offset);
            dom.timerBar.style.stroke = (pLeft <= 5) ? "#e74c3c" : "#fff";
        }

        if (pLeft <= 0) {
            clearInterval(pTimer);
            pTimer = null;
            onPreviewSubmit();
        }
    }, 1000);
}

function renderPreviewAnswers(slide) {
    if (!has(dom.previewAnswers)) return;
    ensureSlideShape(slide);

    dom.previewAnswers.innerHTML = "";
    dom.previewAnswers.style.display = "";

    const localType = slide.type;

    if (localType === "slider") {
        dom.previewAnswers.innerHTML = "";
        dom.previewAnswers.style.display = "block";

        const minV = Number(slide.slider?.min ?? 0);
        const maxV = Number(slide.slider?.max ?? 50);

        const wrap = document.createElement("div");
        wrap.style.width = "min(900px,94%)";
        wrap.style.margin = "0 auto";
        wrap.style.background = "rgba(255,255,255,.92)";
        wrap.style.borderRadius = "14px";
        wrap.style.padding = "14px 14px";
        wrap.style.boxShadow = "0 10px 22px rgba(0,0,0,.20)";
        wrap.innerHTML = `
      <div style="display:flex; align-items:center; gap:14px">
        <div style="width:70px; text-align:center; font-weight:950">Min<br>${minV}</div>
        <input data-action="preview-slider" type="range" min="${minV}" max="${maxV}" value="${minV}" style="width:100%">
        <div style="width:70px; text-align:center; font-weight:950">Max<br>${maxV}</div>
      </div>
      <div class="hint" style="margin-top:10px; font-weight:900; color:#334155">
        Trage slider-ul. Enter = Next (sau așteaptă timer-ul).
      </div>
    `;
        dom.previewAnswers.appendChild(wrap);
        pLastSliderValue = minV;
        return;
    }

    if (localType === "pin") {
        // la pin: răspunsul e click pe imagine; render pin dots in previewMedia
        try {
            dom.previewMedia?.querySelectorAll?.(".pin-dot")?.forEach?.(n => n.remove());
            (slide.pins || []).forEach((p, idx) => {
                const dot = document.createElement("div");
                dot.className = "pin-dot";
                dot.dataset.action = "preview-pin-dot";
                dot.dataset.idx = String(idx);
                dot.style.left = (p.x * 100) + "%";
                dot.style.top = (p.y * 100) + "%";
                dot.textContent = p.label ? String(p.label).slice(0, 2) : String(idx + 1);
                dom.previewMedia?.appendChild(dot);
            });
        } catch {}
        dom.previewAnswers.style.display = "none";
        return;
    }

    let answers = slide.answers || [];
    let imgs = slide.answerImages || [];

    if (localType === "true-false") {
        answers = ["True", "False"];
        imgs = ["", ""];
    }
    if (localType === "open-ended") {
        answers = [answers?.[0] || "..."];
        imgs = [""];
    }

    // Quiz / TrueFalse / OpenEnded preview with selection
    const inputType = (slide.selectType === "multiple" && localType === "quiz") ? "checkbox" : "radio";
    const radioName = "qd_prev_" + String(pIndex);

    answers.forEach((a, i) => {
        const el = document.createElement("div");
        el.className = `answer ${COLORS[i % 4]}`;
        el.style.cursor = "pointer";
        el.dataset.action = "preview-answer";
        el.dataset.idx = String(i);

        el.innerHTML = `
      <div class="answer-left">
        ${imgs[i] ? `<div class="imgbox"><img src="${escapeHtml(imgs[i])}" alt=""></div>` : ""}
        <input class="prev-sel" type="${inputType}" name="${radioName}" data-action="preview-select" data-idx="${i}">
        <div style="font-weight:950;font-size:18px">${escapeHtml(a || (imgs[i] ? "Imagine" : "..."))}</div>
      </div>
      <div class="correctbtn"></div>
    `;
        dom.previewAnswers.appendChild(el);
    });

    // For multiple: show submit button (otherwise click = submit)
    if (localType === "quiz" && slide.selectType === "multiple") {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "btn btn-green";
        btn.style.marginTop = "12px";
        btn.style.width = "min(900px,94%)";
        btn.style.marginLeft = "auto";
        btn.style.marginRight = "auto";
        btn.dataset.action = "preview-submit";
        btn.textContent = "Confirmă";
        dom.previewAnswers.appendChild(btn);
    }

    // small enter animation
    try {
        dom.previewAnswers.classList.remove("is-in");
        requestAnimationFrame(() => dom.previewAnswers.classList.add("is-in"));
    } catch {}
}

function arraysEqualAsSet(a, b) {
    a = Array.isArray(a) ? a.slice().sort() : [];
    b = Array.isArray(b) ? b.slice().sort() : [];
    if (a.length !== b.length) return false;
    return a.every((v, i) => v === b[i]);
}

function validatePreviewAnswer(slide) {
    if (!slide) return false;

    if (slide.type === "slider") {
        const v = Number(pLastSliderValue);
        return Number.isFinite(v) && Number.isFinite(slide.slider?.correct) && v === slide.slider.correct;
    }

    if (slide.type === "pin") {
        return !!pLastPinHitCorrect;
    }

    if (slide.type === "open-ended") {
        // in preview: we don't collect user text; treat as skipped
        return false;
    }

    if (slide.type === "quiz" && slide.selectType === "multiple") {
        const correct = Array.isArray(slide.correctAnswerIndexes) ? slide.correctAnswerIndexes : [];
        return arraysEqualAsSet(correct, pSelected);
    }

    // single choice
    return (pSelected[0] === slide.correctAnswerIndex);
}

function applyScore(correct) {
    if (!correct) return;

    pScore++;
    if (quizData.settings.bonusSpeed) {
        const timeTaken = (Date.now() - pStartTime) / 1000;
        if (timeTaken <= Number(quizData.settings.bonusTime || 5)) {
            pScore += 2;
            showSpeedBonus();
        }
    }
}

function onPreviewSubmit() {
    const slide = quizData.slides[pIndex];
    if (!slide) return;

    if (pTimer) clearInterval(pTimer);
    pTimer = null;

    const correct = validatePreviewAnswer(slide);
    applyScore(correct);
    playAnswerSound(correct ? "correct" : "wrong");
    nextPreview();
}

function nextPreview() {
    if (pIndex < quizData.slides.length - 1) {
        pIndex++;
        startPreviewSlide();
    } else {
        alert(`Felicitări! Ai terminat Quiz-ul.\nScor: ${pScore} / ${quizData.slides.length}`);
        closePreview();
    }
}

function showSpeedBonus(){
    const el = document.createElement("div");
    el.textContent = "⚡ BONUS VITEZĂ!";
    el.style.position = "fixed";
    el.style.top = "18%";
    el.style.left = "50%";
    el.style.transform = "translateX(-50%)";
    el.style.background = "#00c853";
    el.style.color = "#fff";
    el.style.padding = "14px 24px";
    el.style.borderRadius = "12px";
    el.style.fontWeight = "900";
    el.style.zIndex = "9999";
    el.style.boxShadow = "0 12px 26px rgba(0,0,0,.25)";
    document.body.appendChild(el);
    setTimeout(()=>el.remove(), 1300);
}

function playAnswerSound(type){
    const url = type === "correct" ? quizData.settings.correctSound : quizData.settings.wrongSound;
    if (!url) return;
    try {
        const audio = new Audio(url);
        audio.volume = 0.75;
        audio.play().catch(()=>{});
    } catch {}
}

/* -------------------------
   DRAG & DROP (jumble)
-------------------------- */
let dragIdx = null;

function onDragStart(e) {
    const box = e.target.closest('[data-drag="jumble"]');
    if (!box) return;
    dragIdx = Number(box.dataset.idx);
    e.dataTransfer.effectAllowed = "move";
}
function onDragOver(e) {
    if (!e.target.closest('[data-drag="jumble"]')) return;
    e.preventDefault();
}
function onDrop(e) {
    const box = e.target.closest('[data-drag="jumble"]');
    if (!box) return;
    e.preventDefault();

    const targetIdx = Number(box.dataset.idx);
    if (!Number.isFinite(dragIdx) || !Number.isFinite(targetIdx) || dragIdx === targetIdx) return;

    const slide = getSlide();
    if (!slide) return;

    const movedA = slide.answers.splice(dragIdx, 1)[0];
    slide.answers.splice(targetIdx, 0, movedA);

    if (Array.isArray(slide.answerImages)) {
        const movedI = slide.answerImages.splice(dragIdx, 1)[0];
        slide.answerImages.splice(targetIdx, 0, movedI);
    }

    renderAnswers(slide);
    JUMBLE_LAYOUT.apply(slide);
    dragIdx = null;
}

/* -------------------------
   PIN BEHAVIOR (builder + preview)
-------------------------- */
function togglePinCorrect(slide, idx) {
    const pin = slide?.pins?.[idx];
    if (!pin) return;
    pin.correct = !pin.correct;
}
function deletePin(slide, idx) {
    if (!slide?.pins || !Array.isArray(slide.pins)) return;
    slide.pins.splice(idx, 1);
}

/* -------------------------
   EVENTS (delegation)
-------------------------- */
document.addEventListener("click", async (e) => {
    // pin dot click
    const pinDot = e.target.closest?.(".pin-dot");
    if (pinDot) {
        e.preventDefault();
        const slide = getSlide();
        if (!slide || slide.type !== "pin") return;

        const idx = Number(pinDot.dataset.idx);
        if (!Number.isFinite(idx)) return;

        if (e.shiftKey) deletePin(slide, idx);
        else togglePinCorrect(slide, idx);

        renderCanvas();
        return;
    }

    // allow native toggle for preview-select inputs
    if (e.target?.matches?.('input[data-action="preview-select"]')) {
        // let the browser toggle checked; update selection after a microtask
        queueMicrotask(() => {
            const idx = Number(e.target.dataset.idx);
            if (Number.isFinite(idx)) onPreviewSelect(idx, e.target.checked);
        });
        return;
    }

    const btn = e.target.closest("[data-action]");
    if (!btn) return;

    // do not block checkbox/radio defaults inside answer cards
    const action = btn.dataset.action;
    const skipPrevent = (action === "preview-select");
    if (!skipPrevent) e.preventDefault();

    if (action === "add-slide") return addSlide();

    if (action === "select-slide") {
        const id = Number(btn.dataset.id);
        if (Number.isFinite(id)) selectSlide(id);
        return;
    }

    if (action === "delete-slide") {
        const id = Number(btn.dataset.id);
        if (Number.isFinite(id)) deleteSlide(id);
        return;
    }

    if (action === "open-popup") {
        const kind = btn.dataset.popup;
        openPopup(kind);
        return;
    }

    if (action === "close-popup") return closePopup();

    if (action === "set-theme") return setTheme(btn.dataset.url, btn.dataset.name);
    if (action === "set-type") return setType(btn.dataset.type);

    if (action === "open-media") {
        const target = btn.dataset.media; // "center" | "answer" | "cover"
        const idx = btn.dataset.idx ? Number(btn.dataset.idx) : null;
        openMedia(target, idx);
        return;
    }

    if (action === "open-music") return openMusic();
    if (action === "media-pick") return applyMedia(btn.dataset.url);

    if (action === "media-url-ok") {
        const url = qs("#media-url")?.value || "";
        return applyMedia(url);
    }

    if (action === "media-upload") {
        const input = document.createElement("input");
        input.type = "file";

        const isMusic = popupContext.mediaTarget === "music";
        input.accept = isMusic ? "audio/*" : "image/*";

        input.onchange = () => {
            const file = input.files?.[0];
            if (!file) return;
            const r = new FileReader();
            r.onload = () => applyMedia(String(r.result || ""));
            r.readAsDataURL(file);
        };
        input.click();
        return;
    }

    if (action === "remove-answer-img") {
        const idx = Number(btn.dataset.idx);
        if (Number.isFinite(idx)) removeAnswerImg(idx);
        return;
    }

    if (action === "set-correct") {
        const idx = Number(btn.dataset.idx);
        if (Number.isFinite(idx)) setCorrect(idx);
        return;
    }

    if (action === "pin-clear") {
        const slide = getSlide();
        if (!slide || slide.type !== "pin") return;
        slide.pins = [];
        renderCanvas();
        return;
    }

    if (action === "open-quiz-settings") return openQuizSettings();
    if (action === "close-quiz-settings") return closeQuizSettings();
    if (action === "save-quiz-settings") return saveQuizSettings();

    if (action === "save") {
        saveLocal();
        try {
            const res = await saveRemote();
            if (!res?.success) console.warn("Save remote failed:", res);
            alert("Salvat!");
        } catch (err) {
            console.warn(err);
            alert("Salvat local. Remote a eșuat.");
        }
        return;
    }

    if (action === "preview") return openPreview();
    if (action === "close-preview") return closePreview();

    if (action === "preview-answer") {
        const idx = Number(btn.dataset.idx);
        if (!Number.isFinite(idx)) return;

        // click on card = toggle selection
        const input = btn.querySelector('input[data-action="preview-select"]');
        if (input) {
            if (input.type === "radio") {
                input.checked = true;
                onPreviewSelect(idx, true);
                // single: submit immediately
                return onPreviewSubmit();
            }
            if (input.type === "checkbox") {
                input.checked = !input.checked;
                onPreviewSelect(idx, input.checked);
                return;
            }
        }

        // fallback: submit
        pSelected = [idx];
        return onPreviewSubmit();
    }

    if (action === "preview-submit") return onPreviewSubmit();

    if (action === "preview-pin-dot") {
        // optional: clicking dot acts like clicking that spot
        const slide = quizData?.slides?.[pIndex];
        if (!slide || slide.type !== "pin") return;
        const idx = Number(btn.dataset.idx);
        if (!Number.isFinite(idx)) return;
        pLastPinHitCorrect = !!slide.pins?.[idx]?.correct;
        return onPreviewSubmit();
    }
});

function onPreviewSelect(idx, checked) {
    const slide = quizData.slides[pIndex];
    if (!slide) return;

    if (slide.type === "quiz" && slide.selectType === "multiple") {
        if (checked) {
            if (!pSelected.includes(idx)) pSelected.push(idx);
        } else {
            pSelected = pSelected.filter(i => i !== idx);
        }
        return;
    }

    // single
    pSelected = [idx];
}

/* INPUT updates */
document.addEventListener("input", (e) => {
    // title
    if (has(dom.qTitle) && e.target === dom.qTitle) {
        const slide = getSlide();
        if (slide) slide.title = dom.qTitle.value;
        return;
    }

    // answer input
    if (e.target.matches?.('[data-action="answer-input"]')) {
        const slide = getSlide();
        if (!slide) return;
        const idx = Number(e.target.dataset.idx);
        if (!Number.isFinite(idx)) return;
        slide.answers[idx] = e.target.value;
        return;
    }

    // slider builder inputs (in answers area)
    const slide = getSlide();
    if (!slide) return;

    const a = e.target?.dataset?.action;
    if (!a) return;

    if (slide.type === "slider") {
        ensureSlideShape(slide);

        if (a === "slider-min") {
            let v = Number(e.target.value);
            if (!Number.isFinite(v)) v = 0;
            slide.slider.min = v;
            if (slide.slider.max < slide.slider.min) slide.slider.max = slide.slider.min;
            if (slide.slider.correct < slide.slider.min) slide.slider.correct = slide.slider.min;
            renderAnswers(slide);
            JUMBLE_LAYOUT.apply(slide);
        }

        if (a === "slider-max") {
            let v = Number(e.target.value);
            if (!Number.isFinite(v)) v = 50;
            slide.slider.max = v;
            if (slide.slider.max < slide.slider.min) slide.slider.min = slide.slider.max;
            if (slide.slider.correct > slide.slider.max) slide.slider.correct = slide.slider.max;
            renderAnswers(slide);
            JUMBLE_LAYOUT.apply(slide);
        }

        if (a === "slider-correct") {
            let v = Number(e.target.value);
            if (!Number.isFinite(v)) v = slide.slider.min;
            slide.slider.correct = v;
            const valLine = document.querySelector('[data-role="slider-val"]');
            if (valLine) valLine.textContent = String(v);
        }
    }
});

/* jumble DnD */
if (has(dom.answers)) {
    dom.answers.addEventListener("dragstart", onDragStart);
    dom.answers.addEventListener("dragover", onDragOver);
    dom.answers.addEventListener("drop", onDrop);
}

/* pin add (click on media center) */
if (has(dom.mediaCenter)) {
    dom.mediaCenter.addEventListener("click", (e) => {
        const slide = getSlide();
        if (!slide) return;

        // ignore click on pin-dot
        if (e.target.closest(".pin-dot")) return;

        if (slide.type !== "pin") {
            // normal behavior: open media for center
            openMedia("center", null);
            return;
        }

        ensureSlideShape(slide);

        if (!slide.imageCenter) {
            openMedia("center", null);
            return;
        }

        const { x, y } = pxToPercentInEl(dom.mediaCenter, e.clientX, e.clientY);
        slide.pins.push({ x, y, label: String(slide.pins.length + 1), correct: false });
        renderCanvas();
    });
}

/* pin preview: click on image = next */
if (has(dom.previewMedia)) {
    dom.previewMedia.addEventListener("click", (e) => {
        const slide = quizData?.slides?.[pIndex];
        if (!slide || slide.type !== "pin") return;

        ensureSlideShape(slide);
        if (!slide.imageCenter || !Array.isArray(slide.pins) || !slide.pins.length) {
            pLastPinHitCorrect = false;
            return onPreviewSubmit();
        }

        // detect nearest pin within radius
        const { x, y } = pxToPercentInEl(dom.previewMedia, e.clientX, e.clientY);
        let best = { idx: -1, d: 999 };
        slide.pins.forEach((p, idx) => {
            const dx = x - p.x;
            const dy = y - p.y;
            const d = Math.sqrt(dx*dx + dy*dy);
            if (d < best.d) best = { idx, d };
        });

        const HIT_RADIUS = 0.06; // ~6% of image size
        if (best.idx >= 0 && best.d <= HIT_RADIUS) {
            pLastPinHitCorrect = !!slide.pins[best.idx].correct;
        } else {
            pLastPinHitCorrect = false;
        }

        onPreviewSubmit();
    });
}

/* preview slider tracking */
document.addEventListener("input", (e) => {
    if (e.target?.dataset?.action === "preview-slider") {
        const v = Number(e.target.value);
        if (Number.isFinite(v)) pLastSliderValue = v;
    }
});

/* time + bonus */
const timeGrid = qs("#time-grid");
if (timeGrid) {
    timeGrid.addEventListener("change", (e) => {
        const r = e.target.closest('input[name="time"]');
        if (!r) return;
        quizData.settings.timeLimit = r.value;
    });
}
if (has(dom.bonusToggle)) {
    dom.bonusToggle.addEventListener("change", () => {
        quizData.settings.bonusSpeed = dom.bonusToggle.checked;
        if (has(dom.bonusStatus)) dom.bonusStatus.textContent = dom.bonusToggle.checked ? "ON" : "OFF";
    });
}
if (has(dom.bonusRange)) {
    dom.bonusRange.addEventListener("input", () => {
        quizData.settings.bonusTime = Number(dom.bonusRange.value);
        if (has(dom.bonusTimeLabel)) dom.bonusTimeLabel.textContent = dom.bonusRange.value + "s";
    });
}

/* per-slide: multiple answers */
if (has(dom.selectMultiple)) {
    dom.selectMultiple.addEventListener("change", () => {
        const slide = getSlide();
        if (!slide) return;
        if (slide.type !== "quiz") {
            dom.selectMultiple.checked = false;
            return;
        }
        slide.selectType = dom.selectMultiple.checked ? "multiple" : "single";

        // normalize correct fields
        if (slide.selectType === "single") {
            slide.correctAnswerIndexes = Number.isFinite(slide.correctAnswerIndex) ? [slide.correctAnswerIndex] : [];
        } else {
            slide.correctAnswerIndexes = Array.isArray(slide.correctAnswerIndexes) ? slide.correctAnswerIndexes : [];
            slide.correctAnswerIndex = slide.correctAnswerIndexes.length ? slide.correctAnswerIndexes[0] : null;
        }
        renderAnswers(slide);
    });
}

/* UX: ESC close + overlay click close + Enter next for slider */
document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
        try { closePopup(); } catch {}
        try { closeQuizSettings(); } catch {}
        try { closePreview(); } catch {}
    }
    if (e.key === "Enter") {
        const slide = quizData?.slides?.[pIndex];
        if (slide?.type === "slider") {
            try { onPreviewSubmit(); } catch {}
        }
    }
});

["popup", "quizSettings", "preview"].forEach((k) => {
    const el = dom?.[k];
    if (!el) return;
    el.addEventListener("click", (e) => {
        if (e.target === el) {
            try {
                if (k === "popup") closePopup();
                if (k === "quizSettings") closeQuizSettings();
                if (k === "preview") closePreview();
            } catch {}
        }
    });
});

/* resize: reapply jumble layout safely */
window.addEventListener("resize", () => {
    const slide = getSlide();
    if (!slide) return;
    JUMBLE_LAYOUT.apply(slide);
});

/* -------------------------
   INIT
-------------------------- */
(async function init() {
    ensureInit();

    // try remote, fallback local
    let ok = false;
    try { ok = await loadRemote(); } catch { ok = false; }
    if (!ok) loadLocal();

    ensureInit();

    // apply settings to UI
    if (has(dom.bonusToggle)) dom.bonusToggle.checked = !!quizData.settings.bonusSpeed;
    if (has(dom.bonusStatus)) dom.bonusStatus.textContent = dom.bonusToggle?.checked ? "ON" : "OFF";
    if (has(dom.bonusRange)) dom.bonusRange.value = String(quizData.settings.bonusTime || 5);
    if (has(dom.bonusTimeLabel)) dom.bonusTimeLabel.textContent = (dom.bonusRange?.value || "5") + "s";

    // time radio
    const r = document.querySelector(`input[name="time"][value="${quizData.settings.timeLimit}"]`);
    if (r) r.checked = true;

    // cover
    if (quizData.settings.coverImage && has(dom.cover) && has(dom.coverHint)) {
        dom.cover.style.backgroundImage = `url('${quizData.settings.coverImage}')`;
        dom.cover.style.backgroundSize = "cover";
        dom.cover.style.backgroundPosition = "center";
        dom.coverHint.style.display = "none";
    }

    // music reflect at load
    if (has(dom.musicName)) dom.musicName.textContent = quizData.settings.musicUrl ? "Selectat" : "Fără muzică";
    if (has(dom.musicIcon)) dom.musicIcon.src = quizData.settings.musicUrl
        ? "https://quizdigo.com/quizigo/music-on.png"
        : "https://quizdigo.com/quizigo/music.png";

    renderSlides();
    renderCanvas();
})();

/* -------------------------
   CSS (popup grids + pin dots + preview anim)
-------------------------- */
const style = document.createElement("style");
style.textContent = `
  .gridThemes{ display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; }
  .tile{ border:2px solid #eef2f7; border-radius:14px; overflow:hidden; padding:0; cursor:pointer; background:#fff; }
  .tile img{ width:100%; height:90px; object-fit:cover; display:block; }
  .tile span{ display:block; padding:8px; font-weight:900; font-size:12px; color:#334155; }

  .gridTypes{ display:flex; flex-direction:column; gap:10px; }
  .typeTile{ display:flex; gap:12px; align-items:center; text-align:left;
             border:2px solid #eef2f7; border-radius:14px; background:#fff; padding:12px; cursor:pointer; }
  .typeTile:hover{ border-color:#2E85C7; background:#f0f9ff; }
  .typeTile img{ width:56px; height:56px; object-fit:contain; }
  .typeTile strong{ display:block; font-weight:950; color:#0A5084; }
  .typeTile small{ color:#64748b; font-weight:700; }

  .mediaActions{ margin-bottom:12px; }

  .pin-dot{
    position:absolute;
    transform:translate(-50%,-50%);
    width:34px;height:34px;
    border-radius:999px;
    border:3px solid rgba(255,255,255,.95);
    background:rgba(10,80,132,.92);
    color:#fff;
    display:flex;align-items:center;justify-content:center;
    font-weight:950;
    cursor:pointer;
    box-shadow:0 10px 18px rgba(0,0,0,.18), 0 0 0 6px rgba(10,80,132,.18);
    user-select:none;
    z-index:5;
  }

  #preview-answers.is-in{ animation: qdUp .18s ease-out both; }
  @keyframes qdUp{ from{ transform:translateY(14px); opacity:.0; } to{ transform:translateY(0); opacity:1; } }
`;
document.head.appendChild(style);