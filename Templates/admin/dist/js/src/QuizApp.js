import { QuizDom } from './QuizDom.js';
import { GlobalPopup } from './GlobalPopup.js';
import { PopupInteractions } from './PopupInteractions.js';
import { QuizStore } from './QuizStore.js';
import { SlideRenderer } from './SlideRenderer.js';
import { SlideInteractions } from './SlideInteractions.js';
import { CanvasRenderer } from './CanvasRenderer.js';
import { CanvasInteractions } from './CanvasInteractions.js';
import { QuizStorage } from './QuizStorage.js';
import { CONFIG_PATHS } from './constants.js';

export class QuizApp {
  constructor(root = document) {
    this.dom = new QuizDom(root);
    this.store = new QuizStore();
    this.storage = new QuizStorage(this.store);

    this.globalPopup = new GlobalPopup(this.dom);

    this.slideRenderer = new SlideRenderer(this.dom, this.store);
    this.canvasRenderer = new CanvasRenderer(this.dom, this.store);

    this.popupInteractions = new PopupInteractions({
      dom: this.dom,
      popup: this.globalPopup,
      store: this.store,
      canvasRenderer: this.canvasRenderer,
      slideRenderer: this.slideRenderer,
      storage: this.storage,
    });

    this.slideInteractions = new SlideInteractions({
      dom: this.dom,
      store: this.store,
      renderer: this.slideRenderer,
      canvas: this.canvasRenderer,
      storage: this.storage,
      popup: this.globalPopup,
    });

    this.canvasInteractions = new CanvasInteractions({
      dom: this.dom,
      store: this.store,
      canvasRenderer: this.canvasRenderer,
      slideRenderer: this.slideRenderer,
      storage: this.storage,
      popup: this.globalPopup,
    });
  }

  mergeConfigParts(configParts) {
    const [
      coreConfig = {},
      layoutsConfig = {},
      themesConfig = {},
      audioConfig = {},
      questionTypesConfig = {},
      quizzesConfig = {},
    ] = configParts;

    return {
      common: {
        ...(coreConfig.common || {}),
        builder: {
          ...((coreConfig.common || {}).builder || {}),
          ...(layoutsConfig.builder || {}),
        },
      },
      assets: {
        themes: Array.isArray(themesConfig.themes) ? themesConfig.themes : [],
        themeImageLibrary: Array.isArray(themesConfig.themeImageLibrary) ? themesConfig.themeImageLibrary : [],
        audioLibrary: Array.isArray(audioConfig.audioLibrary) ? audioConfig.audioLibrary : [],
      },
      questionTypes: Array.isArray(questionTypesConfig.questionTypes) ? questionTypesConfig.questionTypes : [],
      quizzes: Array.isArray(quizzesConfig.quizzes) ? quizzesConfig.quizzes : [],
    };
  }

  async loadJsonConfig(url) {
    const cacheBustedUrl = `${url}${url.includes('?') ? '&' : '?'}_ts=${Date.now()}`;
    const response = await fetch(cacheBustedUrl, {
      credentials: 'same-origin',
      cache: 'no-store',
    });

    if (!response.ok) {
      throw new Error(`Config load failed: ${response.status} for ${url}`);
    }

    return response.json();
  }

  async loadConfig() {
    try {
      const configParts = await Promise.all([
        this.loadJsonConfig(CONFIG_PATHS.BUILDER_CORE_JSON),
        this.loadJsonConfig(CONFIG_PATHS.BUILDER_LAYOUTS_JSON),
        this.loadJsonConfig(CONFIG_PATHS.BUILDER_THEMES_JSON),
        this.loadJsonConfig(CONFIG_PATHS.BUILDER_AUDIO_JSON),
        this.loadJsonConfig(CONFIG_PATHS.QUESTION_TYPES_JSON),
        this.loadJsonConfig(CONFIG_PATHS.QUIZZES_JSON),
      ]);

      const config = this.mergeConfigParts(configParts);
      this.store.setConfig(config);
    } catch (error) {
      try {
        const legacyConfig = await this.loadJsonConfig(CONFIG_PATHS.QUIZ_LIBRARY_JSON);
        this.store.setConfig(legacyConfig);
      } catch (legacyError) {
        console.warn('Quiz config not loaded:', error, legacyError);
      }
    }
  }

  async init() {
    await this.loadConfig();

    const loaded = this.storage.load();

    if (!loaded) {
      this.store.ensureInit();
      this.storage.save();
    } else {
      this.store.ensureInit();
    }

    this.globalPopup.init();

    this.slideRenderer.renderSlides();
    this.canvasRenderer.render();

    this.popupInteractions.bind();
    this.slideInteractions.bind();
    this.canvasInteractions.bind();
  }
}
