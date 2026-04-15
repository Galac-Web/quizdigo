
import { COLORS, CONFIG_PATHS, STOCK_IMAGES } from './constants.js';
import { CanvasPresetManager } from './CanvasPresetManager.js';

/**
 * Magazinul central pentru datele builder-ului de quiz.
 *
 * Responsabilități:
 * 1. păstrează starea globală a quiz-ului;
 * 2. creează slide-uri și răspunsuri noi;
 * 3. normalizează datele vechi ca să nu se rupă UI-ul după extensii noi;
 * 4. oferă metode clare pentru schimbarea logicii fără a împrăștia codul în view/controller.
 */
export class QuizStore {
  constructor() {
    this.data = {
      settings: {
        themeId: '',
        theme: 'standart',
        themeUrl: '',
        timeLimit: '20s',
        bonusSpeed: true,
        bonusTime: 10,
        title: '',
        description: '',
        visibility: 'private',
        lang: 'ro',
        coverImage: '',
        musicUrl: '',
        correctSound: '',
        wrongSound: '',
        gongStartUrl: '',
        gongEndUrl: '',
        scoreBase: 1000,
        revealMode: 'original',
        mode: 'single',
        screenshotEnabled: true,
      },
      slides: [],
      currentSlideId: null,
      id_quiz: null,
      meta: null,
    };

    this.config = null;
    this.canvasPresetManager = new CanvasPresetManager();
    this.mediaLibraryCache = null;
  }

  setConfig(config) {
    this.config = config || null;
    this.canvasPresetManager.setConfig(this.config);
  }

  getConfig() {
    return this.config || {};
  }

  getCommonConfig() {
    return this.config?.common || {};
  }

  getQuestionTypes() {
    const questionTypes = Array.isArray(this.config?.questionTypes) ? this.config.questionTypes : [];
    return questionTypes;
  }

  getQuestionTypeConfig(typeId) {
    return this.getQuestionTypes().find((questionTypeItem) => questionTypeItem.id === typeId) || null;
  }

  getInteractionConfigForSlide(slideObject) {
    if (!slideObject) {
      return this.getDefaultInteractionConfig('quiz', 'quiz');
    }

    return {
      ...this.getDefaultInteractionConfig(
        slideObject.originalType || slideObject.type || 'quiz',
        slideObject.type === 'media' ? 'media' : 'quiz'
      ),
      ...(slideObject.settings?.interaction || {}),
    };
  }

  getAudioLibrary() {
    return Array.isArray(this.config?.assets?.audioLibrary) ? this.config.assets.audioLibrary : [];
  }

  getConfiguredImageLibrary() {
    const themeImageLibrary = Array.isArray(this.config?.assets?.themeImageLibrary) ? this.config.assets.themeImageLibrary : [];
    const themeUrls = this.getThemesFromConfig()
      .filter((themeItem) => themeItem?.url)
      .map((themeItem) => ({
        id: themeItem.id || themeItem.name || '',
        name: themeItem.name || themeItem.id || 'Theme image',
        url: themeItem.url,
      }));

    const customItems = [...themeImageLibrary, ...themeUrls].filter((imageItem) => imageItem?.url);
    const mergedItems = customItems.length ? customItems : [...customItems, ...STOCK_IMAGES];
    const dedupedMap = new Map();

    mergedItems.forEach((imageItem, imageIndex) => {
      const url = imageItem?.url || '';
      if (!url) return;
      const key = url;
      if (!dedupedMap.has(key)) {
        dedupedMap.set(key, {
          id: imageItem.id || `image_${imageIndex + 1}`,
          name: imageItem.name || imageItem.id || `Image ${imageIndex + 1}`,
          url,
          publicPath: imageItem.publicPath || '',
          serverPath: imageItem.serverPath || '',
        });
      }
    });

    return Array.from(dedupedMap.values());
  }

  async getUploadedImageLibrary() {
    if (Array.isArray(this.mediaLibraryCache)) {
      return this.mediaLibraryCache;
    }

    try {
      const response = await fetch(`${CONFIG_PATHS.CONFIG_BUILDER_API}?action=list_assets&type=image&_ts=${Date.now()}`, { cache: 'no-store' });
      const payload = await response.json();
      this.mediaLibraryCache = Array.isArray(payload?.items) ? payload.items : [];
      return this.mediaLibraryCache;
    } catch (error) {
      this.mediaLibraryCache = [];
      return [];
    }
  }

  async getImageLibraryCatalog() {
    const uploadedItems = await this.getUploadedImageLibrary();
    const mergedItems = [...uploadedItems, ...this.getConfiguredImageLibrary()];
    const dedupedMap = new Map();

    mergedItems.forEach((imageItem, imageIndex) => {
      const url = imageItem?.url || '';
      if (!url) return;
      if (!dedupedMap.has(url)) {
        dedupedMap.set(url, {
          id: imageItem.id || `image_${imageIndex + 1}`,
          name: imageItem.name || imageItem.id || `Image ${imageIndex + 1}`,
          url,
          publicPath: imageItem.publicPath || '',
          serverPath: imageItem.serverPath || '',
        });
      }
    });

    return Array.from(dedupedMap.values());
  }

  getAudioLibraryByCategory(category) {
    return this.getAudioLibrary().filter((audioLibraryItem) => audioLibraryItem.category === category);
  }

  getThemesFromConfig() {
    return this.canvasPresetManager.getThemeCatalog();
  }

  getThemeById(themeId) {
    return this.canvasPresetManager.getThemeById(themeId);
  }

  getActiveThemePreset() {
    const activeThemeId = this.data?.settings?.themeId || '';
    const configuredTheme = this.getThemeById(activeThemeId);

    if (configuredTheme) {
      return configuredTheme;
    }

    const fallbackByName = this.getThemesFromConfig().find((themeItem) => themeItem.name === this.data?.settings?.theme);
    return fallbackByName || this.getThemesFromConfig()[0] || null;
  }

  getLayoutsByType(type = 'quiz') {
    return this.canvasPresetManager.getLayoutsBySlideType(type, this.data?.settings?.lang || 'en');
  }

  getLayoutForSlide(slideObject) {
    return this.canvasPresetManager.getLayoutById(
      slideObject?.type || 'quiz',
      slideObject?.settings?.layoutId || null,
      this.data?.settings?.lang || 'en'
    );
  }

  getResolvedLayoutForSlide(slideObject) {
    return this.canvasPresetManager.resolveLayout(
      slideObject?.type || 'quiz',
      slideObject?.settings?.layoutId || null,
      this.data?.settings?.lang || 'en'
    );
  }

  getData() {
    return this.data;
  }

  setData(data) {
    this.data = data || {};

    if (!this.data.settings) {
      this.data.settings = {
        theme: 'standart',
        themeId: '',
        themeUrl: '',
        timeLimit: '20s',
        bonusSpeed: true,
        bonusTime: 10,
        title: '',
        description: '',
        visibility: 'private',
        lang: 'ro',
        coverImage: '',
        musicUrl: '',
        correctSound: '',
        wrongSound: '',
        gongStartUrl: '',
        gongEndUrl: '',
        scoreBase: 1000,
        revealMode: 'original',
        mode: 'single',
        screenshotEnabled: true,
      };
    }

    if (!Array.isArray(this.data.slides)) {
      this.data.slides = [];
    }

    this.data.slides = this.data.slides.map((slideObject) => this.normalizeSlideStructure(slideObject));

    if (!this.data.currentSlideId && this.data.slides.length) {
      this.data.currentSlideId = this.data.slides[0].id;
    }
  }

  getSlides() {
    return this.data.slides;
  }

  getCurrentSlideId() {
    return this.data.currentSlideId;
  }

  getCurrentSlide() {
    return this.data.slides.find((slideItem) => slideItem.id === this.data.currentSlideId) || null;
  }

  generateSlideId() {
    return Date.now() + Math.floor(Math.random() * 10000);
  }

  getDefaultQuestionLimits() {
    return {
      questionTextMaxLength: 130,
      answerTextMaxLength: 80,
    };
  }

  getDefaultScoringSettings() {
    return {
      basePoints: 1000,
      speedBonus: {
        enabled: true,
        percent: 50,
        withinSeconds: 10,
      },
      wrongAnswerPenalty: {
        enabled: false,
        mode: 'equal_to_correct',
      },
    };
  }

  getDefaultAnswerPresentationSettings() {
    return {
      answerCount: 4,
      layoutPreset: '4x4',
      useMainMediaAsAnswerBackground: false,
    };
  }

  getDefaultTextEntrySettings() {
    return {
      placeholder: 'Tasteaza raspunsul aici...',
      acceptedAnswers: [],
    };
  }

  getDefaultSliderSettings() {
    return {
      min: 0,
      max: 100,
      correct: 50,
      step: 1,
    };
  }

  getDefaultHotspotSettings() {
    return {
      x: 50,
      y: 50,
      tolerance: 12,
    };
  }

  getDefaultDragDropSettings() {
    return {
      zoneCount: 4,
      zoneLabels: ['Zona 1', 'Zona 2', 'Zona 3', 'Zona 4'],
      previewAssignments: {},
    };
  }

  getDefaultMatchPairsSettings() {
    return {
      pairCount: 4,
      rightItems: ['Tinta 1', 'Tinta 2', 'Tinta 3', 'Tinta 4'],
      previewPairs: {},
      activeLeftIndex: null,
    };
  }

  getDefaultCustomRuntimeState() {
    return {
      inputs: {},
      buttons: {},
      lastAction: '',
    };
  }

  getDefaultInteractionRuntimeState() {
    return {
      startedAt: null,
      submittedAt: null,
      isCorrect: null,
      scoreAwarded: 0,
      penaltyApplied: 0,
      message: '',
      selectedAnswers: [],
      textValue: '',
      sliderValue: null,
      hotspotSelection: null,
    };
  }

  ensureInteractionRuntimeState(slideObject) {
    if (!slideObject) return this.getDefaultInteractionRuntimeState();

    slideObject.settings = slideObject.settings || {};
    slideObject.settings.interactionRuntime = {
      ...this.getDefaultInteractionRuntimeState(),
      ...(slideObject.settings.interactionRuntime || {}),
      selectedAnswers: Array.isArray(slideObject.settings.interactionRuntime?.selectedAnswers)
        ? slideObject.settings.interactionRuntime.selectedAnswers.filter((item) => Number.isInteger(item))
        : [],
      hotspotSelection:
        typeof slideObject.settings.interactionRuntime?.hotspotSelection === 'object' &&
        slideObject.settings.interactionRuntime.hotspotSelection !== null
          ? slideObject.settings.interactionRuntime.hotspotSelection
          : null,
    };

    if (!slideObject.settings.interactionRuntime.startedAt) {
      slideObject.settings.interactionRuntime.startedAt = Date.now();
    }

    return slideObject.settings.interactionRuntime;
  }

  resetInteractionRuntimeState(slideObject) {
    if (!slideObject) return this.getDefaultInteractionRuntimeState();

    slideObject.settings = slideObject.settings || {};
    slideObject.settings.interactionRuntime = {
      ...this.getDefaultInteractionRuntimeState(),
      startedAt: Date.now(),
    };

    slideObject.settings.customRuntimeState = {
      ...this.getDefaultCustomRuntimeState(),
    };

    if (slideObject.settings.dragDropConfig) {
      slideObject.settings.dragDropConfig.previewAssignments = {};
    }

    if (slideObject.settings.matchPairsConfig) {
      slideObject.settings.matchPairsConfig.previewPairs = {};
      slideObject.settings.matchPairsConfig.activeLeftIndex = null;
    }

    return slideObject.settings.interactionRuntime;
  }

  normalizeComparableText(inputValue = '') {
    return String(inputValue || '').trim().toLowerCase();
  }

  getEffectiveScoringSettings(slideObject) {
    const currentTypeIdentifier = slideObject?.originalType || slideObject?.type || 'quiz';
    const questionTypeConfigurationObject = this.getQuestionTypeConfig(currentTypeIdentifier);
    const commonScoringObject = this.getCommonConfig()?.defaults?.scoring || {};

    return {
      basePoints: Number(
        slideObject?.settings?.scoring?.basePoints ??
        questionTypeConfigurationObject?.settings?.scoring?.basePoints ??
        commonScoringObject?.basePoints ??
        1000
      ),
      speedBonus: {
        enabled: !!(
          slideObject?.settings?.scoring?.speedBonus?.enabled ??
          questionTypeConfigurationObject?.settings?.scoring?.speedBonus?.enabled ??
          commonScoringObject?.speedBonus?.enabled
        ),
        percent: Number(
          slideObject?.settings?.scoring?.speedBonus?.percent ??
          questionTypeConfigurationObject?.settings?.scoring?.speedBonus?.percent ??
          commonScoringObject?.speedBonus?.percent ??
          50
        ),
        withinSeconds: Number(
          slideObject?.settings?.scoring?.speedBonus?.withinSeconds ??
          questionTypeConfigurationObject?.settings?.scoring?.speedBonus?.withinSeconds ??
          commonScoringObject?.speedBonus?.withinSeconds ??
          10
        ),
      },
      wrongAnswerPenalty: {
        enabled: !!(
          slideObject?.settings?.scoring?.wrongAnswerPenalty?.enabled ??
          questionTypeConfigurationObject?.settings?.scoring?.wrongAnswerPenalty?.enabled ??
          commonScoringObject?.wrongAnswerPenalty?.enabled
        ),
        mode:
          slideObject?.settings?.scoring?.wrongAnswerPenalty?.mode ??
          questionTypeConfigurationObject?.settings?.scoring?.wrongAnswerPenalty?.mode ??
          commonScoringObject?.wrongAnswerPenalty?.mode ??
          'equal_to_correct',
      },
    };
  }

  calculateEvaluationPoints(slideObject, isCorrect, elapsedSeconds = 0) {
    const scoringObject = this.getEffectiveScoringSettings(slideObject);
    const basePointsValue = Number(scoringObject.basePoints || 0);
    let scoreAwardedValue = 0;
    let penaltyAppliedValue = 0;

    if (isCorrect) {
      scoreAwardedValue = basePointsValue;
      if (scoringObject.speedBonus.enabled && elapsedSeconds <= Number(scoringObject.speedBonus.withinSeconds || 0)) {
        scoreAwardedValue += Math.round(basePointsValue * (Number(scoringObject.speedBonus.percent || 0) / 100));
      }
    } else if (scoringObject.wrongAnswerPenalty.enabled) {
      if (scoringObject.wrongAnswerPenalty.mode === 'equal_to_correct') {
        penaltyAppliedValue = basePointsValue;
      }
    }

    return {
      scoreAwarded: scoreAwardedValue,
      penaltyApplied: penaltyAppliedValue,
    };
  }

  evaluateInteractionForSlide(slideObject) {
    if (!slideObject) {
      return { isCorrect: null, scoreAwarded: 0, penaltyApplied: 0, message: 'Nu exista slide activ.' };
    }

    const interactionObject = this.getInteractionConfigForSlide(slideObject);
    const runtimeStateObject = this.ensureInteractionRuntimeState(slideObject);
    const nowTimestamp = Date.now();
    const elapsedSeconds = Math.max(0, Math.round((nowTimestamp - Number(runtimeStateObject.startedAt || nowTimestamp)) / 1000));

    let isCorrect = null;
    let messageText = 'Interactie neconfigurata.';

    if (interactionObject.type === 'choice' || interactionObject.type === 'true_false') {
      const selectedAnswers = [...runtimeStateObject.selectedAnswers].sort((firstIndex, secondIndex) => firstIndex - secondIndex);
      const correctAnswers = (Array.isArray(slideObject.correctAnswerIndexes) ? slideObject.correctAnswerIndexes : [])
        .slice()
        .sort((firstIndex, secondIndex) => firstIndex - secondIndex);
      isCorrect = selectedAnswers.length > 0 &&
        selectedAnswers.length === correctAnswers.length &&
        selectedAnswers.every((answerIndex, itemIndex) => answerIndex === correctAnswers[itemIndex]);
      messageText = isCorrect ? 'Raspunsul selectat este corect.' : 'Selectia nu corespunde raspunsului corect.';
    }

    if (interactionObject.type === 'text_input') {
      const enteredValue = this.normalizeComparableText(runtimeStateObject.textValue);
      const acceptedAnswers = Array.isArray(slideObject.settings?.textEntry?.acceptedAnswers)
        ? slideObject.settings.textEntry.acceptedAnswers.map((item) => this.normalizeComparableText(item)).filter(Boolean)
        : [];
      isCorrect = !!enteredValue && acceptedAnswers.includes(enteredValue);
      messageText = isCorrect ? 'Textul introdus este acceptat.' : 'Textul introdus nu se afla in lista raspunsurilor acceptate.';
    }

    if (interactionObject.type === 'slider') {
      const currentValue = Number(runtimeStateObject.sliderValue ?? slideObject.settings?.sliderConfig?.min ?? 0);
      const correctValue = Number(slideObject.settings?.sliderConfig?.correct ?? slideObject.settings?.sliderCorrect ?? 0);
      isCorrect = currentValue === correctValue;
      messageText = isCorrect ? 'Sliderul este pe valoarea corecta.' : `Valoarea sliderului este ${currentValue}, iar corecta este ${correctValue}.`;
    }

    if (interactionObject.type === 'hotspot') {
      const hotspotSelection = runtimeStateObject.hotspotSelection;
      const hotspotConfigObject = slideObject.settings?.hotspotConfig || {};
      if (hotspotSelection) {
        const deltaX = Number(hotspotSelection.x ?? 0) - Number(hotspotConfigObject.x ?? 50);
        const deltaY = Number(hotspotSelection.y ?? 0) - Number(hotspotConfigObject.y ?? 50);
        const distance = Math.sqrt((deltaX ** 2) + (deltaY ** 2));
        isCorrect = distance <= Number(hotspotConfigObject.tolerance ?? 12);
        messageText = isCorrect ? 'Pinul este in zona corecta.' : `Pinul este in afara tolerantei. Distanta: ${distance.toFixed(1)}.`;
      } else {
        isCorrect = false;
        messageText = 'Nu ai selectat inca un punct pe imagine.';
      }
    }

    if (interactionObject.type === 'drag_drop') {
      const dragDropConfigObject = slideObject.settings?.dragDropConfig || {};
      const assignments = dragDropConfigObject.previewAssignments || {};
      const zoneCount = Number(dragDropConfigObject.zoneCount || 0);
      isCorrect = zoneCount > 0 && Array.from({ length: zoneCount }, (_, zoneIndex) => {
        return Number(assignments[String(zoneIndex)]) === zoneIndex;
      }).every(Boolean);
      messageText = isCorrect ? 'Toate elementele sunt in zonele corecte.' : 'Mai exista elemente puse in zone gresite sau lipsesc legaturi.';
    }

    if (interactionObject.type === 'match_pairs') {
      const matchPairsConfigObject = slideObject.settings?.matchPairsConfig || {};
      const previewPairs = matchPairsConfigObject.previewPairs || {};
      const pairCount = Number(matchPairsConfigObject.pairCount || 0);
      isCorrect = pairCount > 0 && Array.from({ length: pairCount }, (_, leftIndex) => {
        return Number(previewPairs[String(leftIndex)]) === leftIndex;
      }).every(Boolean);
      messageText = isCorrect ? 'Toate perechile sunt corecte.' : 'Perechile nu corespund configuratiei corecte.';
    }

    if (interactionObject.type === 'custom') {
      const customRuntimeStateObject = slideObject.settings?.customRuntimeState || {};
      const selectedAnswers = [...(runtimeStateObject.selectedAnswers || [])].sort((firstIndex, secondIndex) => firstIndex - secondIndex);
      const correctAnswers = (Array.isArray(slideObject.correctAnswerIndexes) ? slideObject.correctAnswerIndexes : [])
        .slice()
        .sort((firstIndex, secondIndex) => firstIndex - secondIndex);
      const acceptedAnswers = Array.isArray(slideObject.settings?.textEntry?.acceptedAnswers)
        ? slideObject.settings.textEntry.acceptedAnswers.map((item) => this.normalizeComparableText(item)).filter(Boolean)
        : [];
      const normalizedTypedValue = this.normalizeComparableText(runtimeStateObject.textValue || '');
      const sliderConfigObject = slideObject.settings?.sliderConfig || {};
      const hotspotConfigObject = slideObject.settings?.hotspotConfig || {};
      const hotspotSelection = runtimeStateObject.hotspotSelection;
      const dragDropConfigObject = slideObject.settings?.dragDropConfig || {};
      const assignments = dragDropConfigObject.previewAssignments || {};
      const zoneCount = Number(dragDropConfigObject.zoneCount || 0);
      const matchPairsConfigObject = slideObject.settings?.matchPairsConfig || {};
      const previewPairs = matchPairsConfigObject.previewPairs || {};
      const pairCount = Number(matchPairsConfigObject.pairCount || 0);
      const hasAnyInteraction = Object.keys(customRuntimeStateObject.inputs || {}).length > 0 || Object.keys(customRuntimeStateObject.buttons || {}).length > 0;

      if (correctAnswers.length) {
        isCorrect = selectedAnswers.length > 0 &&
          selectedAnswers.length === correctAnswers.length &&
          selectedAnswers.every((selectedIndex, positionIndex) => selectedIndex === correctAnswers[positionIndex]);
        messageText = isCorrect
          ? 'Template-ul custom a validat raspunsurile selectate.'
          : 'Selectia din template nu corespunde raspunsurilor corecte.';
      } else if (acceptedAnswers.length) {
        isCorrect = acceptedAnswers.includes(normalizedTypedValue);
        messageText = isCorrect
          ? 'Template-ul custom a validat textul introdus.'
          : 'Textul introdus in template nu este in lista raspunsurilor acceptate.';
      } else if (runtimeStateObject.sliderValue !== null && runtimeStateObject.sliderValue !== undefined) {
        isCorrect = Number(runtimeStateObject.sliderValue) === Number(sliderConfigObject.correct ?? 50);
        messageText = isCorrect
          ? 'Template-ul custom a validat valoarea sliderului.'
          : 'Valoarea sliderului din template nu este corecta.';
      } else if (hotspotSelection) {
        const deltaX = Number(hotspotSelection.x ?? 0) - Number(hotspotConfigObject.x ?? 50);
        const deltaY = Number(hotspotSelection.y ?? 0) - Number(hotspotConfigObject.y ?? 50);
        const distance = Math.sqrt((deltaX ** 2) + (deltaY ** 2));
        isCorrect = distance <= Number(hotspotConfigObject.tolerance ?? 12);
        messageText = isCorrect
          ? 'Template-ul custom a validat hotspotul.'
          : `Hotspotul din template este in afara tolerantei. Distanta: ${distance.toFixed(1)}.`;
      } else if (zoneCount > 0 && Object.keys(assignments).length) {
        isCorrect = Array.from({ length: zoneCount }, (_, zoneIndex) => Number(assignments[String(zoneIndex)]) === zoneIndex).every(Boolean);
        messageText = isCorrect
          ? 'Template-ul custom a validat zonele drag & drop.'
          : 'Maparea drag & drop din template nu este corecta.';
      } else if (pairCount > 0 && Object.keys(previewPairs).length) {
        isCorrect = Array.from({ length: pairCount }, (_, leftIndex) => Number(previewPairs[String(leftIndex)]) === leftIndex).every(Boolean);
        messageText = isCorrect
          ? 'Template-ul custom a validat perechile.'
          : 'Perechile din template nu corespund configuratiei.';
      } else {
        isCorrect = hasAnyInteraction;
        messageText = hasAnyInteraction
          ? 'Interactiunea custom a fost declansata. Adauga reguli de validare prin raspunsuri, text, slider, hotspot sau mapping.'
          : 'Nu exista inca actiune custom executata.';
      }
    }

    if (interactionObject.type === 'info_only') {
      isCorrect = true;
      messageText = 'Slide informational fara evaluare de raspuns.';
    }

    const pointsObject = this.calculateEvaluationPoints(slideObject, !!isCorrect, elapsedSeconds);
    runtimeStateObject.submittedAt = nowTimestamp;
    runtimeStateObject.isCorrect = !!isCorrect;
    runtimeStateObject.scoreAwarded = pointsObject.scoreAwarded;
    runtimeStateObject.penaltyApplied = pointsObject.penaltyApplied;
    runtimeStateObject.message = messageText;

    return {
      isCorrect: runtimeStateObject.isCorrect,
      scoreAwarded: runtimeStateObject.scoreAwarded,
      penaltyApplied: runtimeStateObject.penaltyApplied,
      message: runtimeStateObject.message,
      elapsedSeconds,
    };
  }

  ensureStringListLength(inputList = [], requiredLength = 0, labelPrefix = 'Item') {
    const normalizedList = Array.from({ length: requiredLength }, (_, itemIndex) => {
      const existingValue = Array.isArray(inputList) ? inputList[itemIndex] : '';
      return String(existingValue || `${labelPrefix} ${itemIndex + 1}`);
    });

    return normalizedList;
  }

  getDefaultInteractionConfig(typeId = 'quiz', slideType = 'quiz') {
    if (slideType === 'media') {
      return {
        type: 'info_only',
        widget: 'custom_blocks',
        sourceRole: 'media',
        targetRole: 'none',
        confirmButton: false,
        autoEvaluate: false,
        notes: '',
      };
    }

    if (typeId === 'true-false') {
      return {
        type: 'true_false',
        widget: 'true_false_buttons',
        sourceRole: 'answers',
        targetRole: 'answers',
        confirmButton: true,
        autoEvaluate: false,
        notes: '',
      };
    }

    if (typeId === 'open-ended') {
      return {
        type: 'text_input',
        widget: 'text_field',
        sourceRole: 'description',
        targetRole: 'none',
        confirmButton: true,
        autoEvaluate: false,
        notes: '',
      };
    }

    if (typeId === 'slider') {
      return {
        type: 'slider',
        widget: 'range_slider',
        sourceRole: 'answers',
        targetRole: 'none',
        confirmButton: true,
        autoEvaluate: false,
        notes: '',
      };
    }

    if (typeId === 'puzzle') {
      return {
        type: 'drag_drop',
        widget: 'dropzones',
        sourceRole: 'answers',
        targetRole: 'dropzone',
        confirmButton: true,
        autoEvaluate: false,
        notes: '',
      };
    }

    if (typeId === 'pin') {
      return {
        type: 'hotspot',
        widget: 'hotspots',
        sourceRole: 'media',
        targetRole: 'media',
        confirmButton: true,
        autoEvaluate: false,
        notes: '',
      };
    }

    return {
      type: 'choice',
      widget: 'answer_buttons',
      sourceRole: 'answers',
      targetRole: 'answers',
      confirmButton: true,
      autoEvaluate: false,
      notes: '',
    };
  }

  createAnswerObject(answerIndex = 0, initialAnswerText = '') {
    return {
      text: initialAnswerText,
      imageUrl: '',
      correct: false,
      color: COLORS[answerIndex % COLORS.length],
    };
  }

  normalizeAnswerObject(answerValue, answerIndex = 0) {
    if (typeof answerValue === 'object' && answerValue !== null) {
      return {
        text: answerValue.text || '',
        imageUrl: answerValue.imageUrl || answerValue.image || '',
        correct: !!answerValue.correct,
        color: answerValue.color || COLORS[answerIndex % COLORS.length],
      };
    }

    return this.createAnswerObject(answerIndex, typeof answerValue === 'string' ? answerValue : '');
  }

  createQuizAnswers(answerCount = 4) {
    return Array.from({ length: answerCount }, (_, answerIndex) => {
      return this.createAnswerObject(answerIndex, '');
    });
  }

  normalizeSlideStructure(slideObject) {
    const normalizedSlideObject = slideObject || {};

    normalizedSlideObject.settings = normalizedSlideObject.settings || {};
    this.canvasPresetManager.normalizeSlideSettings(normalizedSlideObject, this.data?.settings?.lang || 'en');
    normalizedSlideObject.settings.limits = normalizedSlideObject.settings.limits || this.getDefaultQuestionLimits();
    normalizedSlideObject.settings.interaction = {
      ...this.getDefaultInteractionConfig(
        normalizedSlideObject.originalType || normalizedSlideObject.type || 'quiz',
        normalizedSlideObject.type === 'media' ? 'media' : 'quiz'
      ),
      ...(normalizedSlideObject.settings.interaction || {}),
    };
    normalizedSlideObject.settings.answerPresentation = {
      ...this.getDefaultAnswerPresentationSettings(),
      ...(normalizedSlideObject.settings.answerPresentation || {}),
    };
    normalizedSlideObject.settings.scoring = {
      ...this.getDefaultScoringSettings(),
      ...(normalizedSlideObject.settings.scoring || {}),
      speedBonus: {
        ...this.getDefaultScoringSettings().speedBonus,
        ...(normalizedSlideObject.settings.scoring?.speedBonus || {}),
      },
      wrongAnswerPenalty: {
        ...this.getDefaultScoringSettings().wrongAnswerPenalty,
        ...(normalizedSlideObject.settings.scoring?.wrongAnswerPenalty || {}),
      },
    };
    normalizedSlideObject.settings.textEntry = {
      ...this.getDefaultTextEntrySettings(),
      ...(normalizedSlideObject.settings.textEntry || {}),
    };
    normalizedSlideObject.settings.sliderConfig = {
      ...this.getDefaultSliderSettings(),
      ...(normalizedSlideObject.settings.sliderConfig || {}),
      min: Number(normalizedSlideObject.settings.sliderConfig?.min ?? normalizedSlideObject.settings.sliderMin ?? 0),
      max: Number(normalizedSlideObject.settings.sliderConfig?.max ?? normalizedSlideObject.settings.sliderMax ?? 100),
      correct: Number(normalizedSlideObject.settings.sliderConfig?.correct ?? normalizedSlideObject.settings.sliderCorrect ?? 50),
      step: Number(normalizedSlideObject.settings.sliderConfig?.step ?? 1),
    };
    normalizedSlideObject.settings.sliderMin = normalizedSlideObject.settings.sliderConfig.min;
    normalizedSlideObject.settings.sliderMax = normalizedSlideObject.settings.sliderConfig.max;
    normalizedSlideObject.settings.sliderCorrect = normalizedSlideObject.settings.sliderConfig.correct;
    normalizedSlideObject.settings.hotspotConfig = {
      ...this.getDefaultHotspotSettings(),
      ...(normalizedSlideObject.settings.hotspotConfig || {}),
      x: Number(normalizedSlideObject.settings.hotspotConfig?.x ?? 50),
      y: Number(normalizedSlideObject.settings.hotspotConfig?.y ?? 50),
      tolerance: Number(normalizedSlideObject.settings.hotspotConfig?.tolerance ?? 12),
    };
    normalizedSlideObject.settings.dragDropConfig = {
      ...this.getDefaultDragDropSettings(),
      ...(normalizedSlideObject.settings.dragDropConfig || {}),
    };
    normalizedSlideObject.settings.dragDropConfig.zoneCount = Number(normalizedSlideObject.settings.dragDropConfig?.zoneCount ?? 4) || 4;
    normalizedSlideObject.settings.dragDropConfig.zoneLabels = this.ensureStringListLength(
      normalizedSlideObject.settings.dragDropConfig.zoneLabels,
      normalizedSlideObject.settings.dragDropConfig.zoneCount,
      'Zona'
    );
    normalizedSlideObject.settings.dragDropConfig.previewAssignments =
      typeof normalizedSlideObject.settings.dragDropConfig.previewAssignments === 'object' &&
      normalizedSlideObject.settings.dragDropConfig.previewAssignments !== null
        ? normalizedSlideObject.settings.dragDropConfig.previewAssignments
        : {};
    normalizedSlideObject.settings.matchPairsConfig = {
      ...this.getDefaultMatchPairsSettings(),
      ...(normalizedSlideObject.settings.matchPairsConfig || {}),
    };
    normalizedSlideObject.settings.matchPairsConfig.pairCount = Number(normalizedSlideObject.settings.matchPairsConfig?.pairCount ?? 4) || 4;
    normalizedSlideObject.settings.matchPairsConfig.rightItems = this.ensureStringListLength(
      normalizedSlideObject.settings.matchPairsConfig.rightItems,
      normalizedSlideObject.settings.matchPairsConfig.pairCount,
      'Tinta'
    );
    normalizedSlideObject.settings.matchPairsConfig.previewPairs =
      typeof normalizedSlideObject.settings.matchPairsConfig.previewPairs === 'object' &&
      normalizedSlideObject.settings.matchPairsConfig.previewPairs !== null
        ? normalizedSlideObject.settings.matchPairsConfig.previewPairs
        : {};
    normalizedSlideObject.settings.matchPairsConfig.activeLeftIndex =
      Number.isInteger(normalizedSlideObject.settings.matchPairsConfig.activeLeftIndex)
        ? normalizedSlideObject.settings.matchPairsConfig.activeLeftIndex
        : null;
    normalizedSlideObject.settings.customRuntimeState = {
      ...this.getDefaultCustomRuntimeState(),
      ...(normalizedSlideObject.settings.customRuntimeState || {}),
      inputs:
        typeof normalizedSlideObject.settings.customRuntimeState?.inputs === 'object' &&
        normalizedSlideObject.settings.customRuntimeState.inputs !== null
          ? normalizedSlideObject.settings.customRuntimeState.inputs
          : {},
      buttons:
        typeof normalizedSlideObject.settings.customRuntimeState?.buttons === 'object' &&
        normalizedSlideObject.settings.customRuntimeState.buttons !== null
          ? normalizedSlideObject.settings.customRuntimeState.buttons
          : {},
    };
    normalizedSlideObject.settings.interactionRuntime = {
      ...this.getDefaultInteractionRuntimeState(),
      ...(normalizedSlideObject.settings.interactionRuntime || {}),
      selectedAnswers: Array.isArray(normalizedSlideObject.settings.interactionRuntime?.selectedAnswers)
        ? normalizedSlideObject.settings.interactionRuntime.selectedAnswers.filter((item) => Number.isInteger(item))
        : [],
      hotspotSelection:
        typeof normalizedSlideObject.settings.interactionRuntime?.hotspotSelection === 'object' &&
        normalizedSlideObject.settings.interactionRuntime.hotspotSelection !== null
          ? normalizedSlideObject.settings.interactionRuntime.hotspotSelection
          : null,
    };

    const interactionType = normalizedSlideObject.settings.interaction?.type || 'choice';

    if (normalizedSlideObject.type === 'quiz' || normalizedSlideObject.originalType === 'quiz' || !normalizedSlideObject.type) {
      let existingAnswersCollection = Array.isArray(normalizedSlideObject.answers)
        ? normalizedSlideObject.answers
        : this.createQuizAnswers(normalizedSlideObject.settings.answerPresentation.answerCount || 4);

      if (interactionType === 'true_false') {
        existingAnswersCollection = existingAnswersCollection.length ? existingAnswersCollection.slice(0, 2) : [
          this.createAnswerObject(0, 'Adevarat'),
          this.createAnswerObject(1, 'Fals'),
        ];
      }

      if (interactionType === 'text_input' || interactionType === 'slider' || interactionType === 'hotspot') {
        existingAnswersCollection = [];
      }

      if (interactionType === 'drag_drop') {
        existingAnswersCollection = existingAnswersCollection.length
          ? existingAnswersCollection.slice(0, normalizedSlideObject.settings.dragDropConfig.zoneCount)
          : this.createQuizAnswers(normalizedSlideObject.settings.dragDropConfig.zoneCount);
      }

      if (interactionType === 'match_pairs') {
        existingAnswersCollection = existingAnswersCollection.length
          ? existingAnswersCollection.slice(0, normalizedSlideObject.settings.matchPairsConfig.pairCount)
          : this.createQuizAnswers(normalizedSlideObject.settings.matchPairsConfig.pairCount);
      }

      normalizedSlideObject.answers = existingAnswersCollection.map((singleAnswerObject, answerIndex) => {
        return this.normalizeAnswerObject(singleAnswerObject, answerIndex);
      });

      if (!normalizedSlideObject.answers.length && !['text_input', 'slider', 'hotspot'].includes(interactionType)) {
        normalizedSlideObject.answers = this.createQuizAnswers(normalizedSlideObject.settings.answerPresentation.answerCount || 4);
      }

      if (interactionType === 'true_false') {
        normalizedSlideObject.answers = normalizedSlideObject.answers.slice(0, 2).map((singleAnswerObject, answerIndex) => ({
          ...this.normalizeAnswerObject(singleAnswerObject, answerIndex),
          text: singleAnswerObject?.text || (answerIndex === 0 ? 'Adevarat' : 'Fals'),
        }));
        normalizedSlideObject.selectType = 'single';
      }

      normalizedSlideObject.settings.answerPresentation.answerCount = normalizedSlideObject.answers.length;
      normalizedSlideObject.settings.answerPresentation.layoutPreset = `${normalizedSlideObject.answers.length}x${normalizedSlideObject.answers.length}`;

      normalizedSlideObject.correctAnswerIndexes = Array.isArray(normalizedSlideObject.correctAnswerIndexes)
        ? normalizedSlideObject.correctAnswerIndexes.filter((answerIndex) => Number.isInteger(answerIndex))
        : [];

      if (normalizedSlideObject.correctAnswerIndexes.length) {
        normalizedSlideObject.answers.forEach((singleAnswerObject, answerIndex) => {
          singleAnswerObject.correct = normalizedSlideObject.correctAnswerIndexes.includes(answerIndex);
        });
      } else if (Number.isInteger(normalizedSlideObject.correctAnswerIndex)) {
        normalizedSlideObject.correctAnswerIndexes = [normalizedSlideObject.correctAnswerIndex];
        normalizedSlideObject.answers.forEach((singleAnswerObject, answerIndex) => {
          singleAnswerObject.correct = answerIndex === normalizedSlideObject.correctAnswerIndex;
        });
      }

      if (!normalizedSlideObject.answers.length) {
        normalizedSlideObject.correctAnswerIndexes = [];
        normalizedSlideObject.correctAnswerIndex = null;
      }
    }

    return normalizedSlideObject;
  }

  createQuizSlide() {
    return this.normalizeSlideStructure({
      id: this.generateSlideId(),
      type: 'quiz',
      originalType: 'quiz',
      title: '',
      description: '',
      background: this.data.settings?.themeUrl || '',
      imageCenter: '',
      media: null,
      answers: this.createQuizAnswers(4),
      answerImages: ['', '', '', ''],
      correctAnswerIndex: null,
      correctAnswerIndexes: [],
      selectType: 'single',
      infoMode: false,
      linkUrl: '',
      linkLabel: '',
      settings: {
        layoutId: this.canvasPresetManager.getDefaultLayoutId('quiz'),
        limits: this.getDefaultQuestionLimits(),
        timer: { selected: '20s' },
        scoring: this.getDefaultScoringSettings(),
        audio: {},
        interaction: this.getDefaultInteractionConfig('quiz', 'quiz'),
        imageReveal: { mode: 'original' },
        answerPresentation: this.getDefaultAnswerPresentationSettings(),
        textEntry: this.getDefaultTextEntrySettings(),
        sliderConfig: this.getDefaultSliderSettings(),
        hotspotConfig: this.getDefaultHotspotSettings(),
        dragDropConfig: this.getDefaultDragDropSettings(),
        matchPairsConfig: this.getDefaultMatchPairsSettings(),
        customRuntimeState: this.getDefaultCustomRuntimeState(),
        interactionRuntime: this.getDefaultInteractionRuntimeState(),
      }
    });
  }

  createMediaSlide() {
    return this.normalizeSlideStructure({
      id: this.generateSlideId(),
      type: 'media',
      originalType: 'media',
      title: '',
      description: '',
      background: this.data.settings?.themeUrl || '',
      imageCenter: '',
      media: null,
      answers: [],
      answerImages: [],
      correctAnswerIndex: null,
      correctAnswerIndexes: [],
      selectType: 'single',
      infoMode: true,
      linkUrl: '',
      linkLabel: '',
      settings: {
        layoutId: this.canvasPresetManager.getDefaultLayoutId('media'),
        limits: this.getDefaultQuestionLimits(),
        scoring: this.getDefaultScoringSettings(),
        mediaLayout: 'big-media',
        mediaTextPosition: 'bottom',
        interaction: this.getDefaultInteractionConfig('media', 'media'),
        textEntry: this.getDefaultTextEntrySettings(),
        sliderConfig: this.getDefaultSliderSettings(),
        hotspotConfig: this.getDefaultHotspotSettings(),
        dragDropConfig: this.getDefaultDragDropSettings(),
        matchPairsConfig: this.getDefaultMatchPairsSettings(),
        customRuntimeState: this.getDefaultCustomRuntimeState(),
        interactionRuntime: this.getDefaultInteractionRuntimeState(),
      }
    });
  }

  createSlide() {
    return this.createQuizSlide();
  }

  ensureInit() {
    if (!Array.isArray(this.data.slides)) {
      this.data.slides = [];
    }

    if (!this.data.slides.length) {
      const firstSlide = this.createQuizSlide();
      this.data.slides.push(firstSlide);
      this.data.currentSlideId = firstSlide.id;
      return;
    }

    this.data.slides = this.data.slides.map((slideObject) => this.normalizeSlideStructure(slideObject));

    if (!this.data.currentSlideId) {
      this.data.currentSlideId = this.data.slides[0].id;
    }
  }

  addQuizSlide() {
    const slide = this.createQuizSlide();
    this.data.slides.push(slide);
    this.data.currentSlideId = slide.id;
    return slide;
  }

  addMediaSlide() {
    const slide = this.createMediaSlide();
    this.data.slides.push(slide);
    this.data.currentSlideId = slide.id;
    return slide;
  }

  addSlide() {
    return this.addQuizSlide();
  }

  selectSlide(id) {
    const exists = this.data.slides.some((slideItem) => slideItem.id === id);
    if (!exists) return false;
    this.data.currentSlideId = id;
    return true;
  }

  deleteSlide(id) {
    if (this.data.slides.length <= 1) return false;

    const index = this.data.slides.findIndex((slideItem) => slideItem.id === id);
    if (index === -1) return false;

    this.data.slides.splice(index, 1);

    if (this.data.currentSlideId === id) {
      const fallbackSlideObject = this.data.slides[index] || this.data.slides[index - 1] || this.data.slides[0] || null;
      this.data.currentSlideId = fallbackSlideObject ? fallbackSlideObject.id : null;
    }

    return true;
  }

  duplicateSlide(id) {
    const originalSlideObject = this.data.slides.find((slideItem) => slideItem.id === id);
    if (!originalSlideObject) return null;

    const duplicatedSlideObject = JSON.parse(JSON.stringify(originalSlideObject));
    duplicatedSlideObject.id = this.generateSlideId();

    const originalSlideIndex = this.data.slides.findIndex((slideItem) => slideItem.id === id);
    this.data.slides.splice(originalSlideIndex + 1, 0, duplicatedSlideObject);
    this.data.currentSlideId = duplicatedSlideObject.id;

    return duplicatedSlideObject;
  }

  applyTheme(theme) {
    if (!theme) return;

    this.data.settings.themeId = theme.id || '';
    this.data.settings.theme = theme.name || 'standart';
    this.data.settings.themeUrl = theme.url || '';

    const currentSlideObject = this.getCurrentSlide();
    if (currentSlideObject) {
      currentSlideObject.background = theme.url || '';
    }
  }

  getQuestionTypeMeta(type = 'quiz') {
    const configuredType = this.getQuestionTypeConfig(type);
    if (configuredType) {
      return {
        id: configuredType.id || type,
        name: configuredType.name || type,
        icon: configuredType.icon || 'https://quizdigo.com/quizigo/11.png',
      };
    }

    const map = {
      quiz: { id: 'quiz', name: 'Quiz (Grilă)', icon: 'https://quizdigo.com/quizigo/11.png' },
      media: { id: 'media', name: 'Media / Informational', icon: 'https://quizdigo.com/quizigo/66.png' },
      'true-false': { id: 'true-false', name: 'True / False', icon: 'https://quizdigo.com/quizigo/22.png' },
      'open-ended': { id: 'open-ended', name: 'Open ended', icon: 'https://quizdigo.com/quizigo/33.png' },
      slider: { id: 'slider', name: 'Slider', icon: 'https://quizdigo.com/quizigo/55.png' },
      puzzle: { id: 'puzzle', name: 'Puzzle', icon: 'https://quizdigo.com/quizigo/44.png' },
      pin: { id: 'pin', name: 'Pin answer', icon: 'https://quizdigo.com/quizigo/66.png' },
    };

    return map[type] || map.quiz;
  }

  applyQuestionType(typeId) {
    const slide = this.getCurrentSlide();
    if (!slide) return null;

    if (typeId === 'quiz') {
      slide.type = 'quiz';
      slide.originalType = 'quiz';
      slide.infoMode = false;
      slide.answers = this.createQuizAnswers(slide.settings?.answerPresentation?.answerCount || 4);
      slide.answerImages = ['', '', '', ''];
      slide.correctAnswerIndex = null;
      slide.correctAnswerIndexes = [];
      slide.selectType = 'single';
      slide.settings = slide.settings || {};
      slide.settings.layoutId = this.canvasPresetManager.getDefaultLayoutId('quiz');
      slide.settings.answerPresentation = {
        ...this.getDefaultAnswerPresentationSettings(),
        ...(slide.settings.answerPresentation || {}),
      };
      return slide;
    }

    if (typeId === 'media') {
      slide.type = 'media';
      slide.originalType = 'media';
      slide.infoMode = true;
      slide.answers = [];
      slide.answerImages = [];
      slide.correctAnswerIndex = null;
      slide.correctAnswerIndexes = [];
      slide.selectType = 'single';
      slide.settings = slide.settings || {};
      slide.settings.layoutId = this.canvasPresetManager.getDefaultLayoutId('media');
      slide.settings.mediaLayout = slide.settings.mediaLayout || 'big-media';
      slide.settings.mediaTextPosition = slide.settings.mediaTextPosition || 'bottom';
      return slide;
    }

    return slide;
  }

  setSlideMedia(media) {
    const slide = this.getCurrentSlide();
    if (!slide) return;

    slide.media = media || null;

    if (media && (media.type === 'image' || media.type === 'gif')) {
      slide.imageCenter = media.url || '';
    }
  }

  setAnswerCountForCurrentSlide(requestedAnswerCount) {
    const slide = this.getCurrentSlide();
    if (!slide || slide.type !== 'quiz') return false;

    const normalizedAnswerCount = Number(requestedAnswerCount);
    if (![2, 4, 6].includes(normalizedAnswerCount)) return false;

    const existingAnswersCollection = Array.isArray(slide.answers) ? slide.answers : [];
    const resizedAnswersCollection = [];

    for (let answerIndex = 0; answerIndex < normalizedAnswerCount; answerIndex += 1) {
      const existingAnswerObject = existingAnswersCollection[answerIndex];
      resizedAnswersCollection.push(this.normalizeAnswerObject(existingAnswerObject, answerIndex));
    }

    slide.answers = resizedAnswersCollection;
    slide.settings = slide.settings || {};
    slide.settings.answerPresentation = {
      ...this.getDefaultAnswerPresentationSettings(),
      ...(slide.settings.answerPresentation || {}),
      answerCount: normalizedAnswerCount,
      layoutPreset: `${normalizedAnswerCount}x${normalizedAnswerCount}`,
    };

    slide.correctAnswerIndexes = (Array.isArray(slide.correctAnswerIndexes) ? slide.correctAnswerIndexes : [])
      .filter((answerIndex) => answerIndex < normalizedAnswerCount);

    if (slide.selectType !== 'multiple') {
      slide.correctAnswerIndexes = slide.correctAnswerIndexes.length ? [slide.correctAnswerIndexes[0]] : [];
    }

    slide.correctAnswerIndex = slide.correctAnswerIndexes.length ? slide.correctAnswerIndexes[0] : null;

    slide.answers.forEach((singleAnswerObject, answerIndex) => {
      singleAnswerObject.correct = slide.correctAnswerIndexes.includes(answerIndex);
    });

    return true;
  }

  setAnswerImageUrlForCurrentSlide(answerIndex, imageUrlValue) {
    const slide = this.getCurrentSlide();
    if (!slide || !Array.isArray(slide.answers) || !slide.answers[answerIndex]) return false;

    slide.answers[answerIndex] = this.normalizeAnswerObject(slide.answers[answerIndex], answerIndex);
    slide.answers[answerIndex].imageUrl = imageUrlValue || '';
    return true;
  }

  setAnswerTextForCurrentSlide(answerIndex, answerTextValue) {
    const slide = this.getCurrentSlide();
    if (!slide || !Array.isArray(slide.answers) || !slide.answers[answerIndex]) return false;

    slide.answers[answerIndex] = this.normalizeAnswerObject(slide.answers[answerIndex], answerIndex);
    slide.answers[answerIndex].text = answerTextValue || '';
    return true;
  }

  setMainImageSliceModeForCurrentSlide(shouldUseMainImageSliceMode) {
    const slide = this.getCurrentSlide();
    if (!slide || slide.type !== 'quiz') return false;

    slide.settings = slide.settings || {};
    slide.settings.answerPresentation = {
      ...this.getDefaultAnswerPresentationSettings(),
      ...(slide.settings.answerPresentation || {}),
      useMainMediaAsAnswerBackground: !!shouldUseMainImageSliceMode,
      layoutPreset: `${slide.answers?.length || 4}x${slide.answers?.length || 4}`,
      answerCount: slide.answers?.length || 4,
    };

    return true;
  }

  setLayoutForCurrentSlide(layoutId) {
    const slide = this.getCurrentSlide();
    if (!slide || !layoutId) return false;

    slide.settings = slide.settings || {};
    const layout = this.canvasPresetManager.getLayoutById(slide.type || 'quiz', layoutId, this.data?.settings?.lang || 'en');
    if (!layout) return false;

    slide.settings.layoutId = layout.id;

    if (slide.type === 'quiz' && Array.isArray(layout.supportsAnswers) && layout.supportsAnswers.length) {
      const currentAnswerCount = Array.isArray(slide.answers) ? slide.answers.length : 0;
      if (!layout.supportsAnswers.includes(currentAnswerCount)) {
        this.setAnswerCountForCurrentSlide(layout.supportsAnswers[0]);
      }
    }

    return true;
  }

  toggleCorrectAnswerSelectionForCurrentSlide(answerIndex) {
    const slide = this.getCurrentSlide();
    if (!slide || !Array.isArray(slide.answers) || !slide.answers[answerIndex]) return false;

    if (slide.selectType === 'multiple') {
      const currentMultipleSelectionIndexes = new Set(
        Array.isArray(slide.correctAnswerIndexes) ? slide.correctAnswerIndexes : []
      );

      if (currentMultipleSelectionIndexes.has(answerIndex)) {
        currentMultipleSelectionIndexes.delete(answerIndex);
      } else {
        currentMultipleSelectionIndexes.add(answerIndex);
      }

      slide.correctAnswerIndexes = Array.from(currentMultipleSelectionIndexes).sort((firstIndex, secondIndex) => firstIndex - secondIndex);
      slide.correctAnswerIndex = slide.correctAnswerIndexes.length ? slide.correctAnswerIndexes[0] : null;
      slide.answers.forEach((singleAnswerObject, currentAnswerIndex) => {
        singleAnswerObject.correct = slide.correctAnswerIndexes.includes(currentAnswerIndex);
      });

      return true;
    }

    slide.correctAnswerIndex = answerIndex;
    slide.correctAnswerIndexes = [answerIndex];
    slide.answers.forEach((singleAnswerObject, currentAnswerIndex) => {
      singleAnswerObject.correct = currentAnswerIndex === answerIndex;
    });

    return true;
  }

  applyConfiguredQuestionType(typeId) {
    const slide = this.getCurrentSlide();
    if (!slide) return null;

    const configuredType = this.getQuestionTypeConfig(typeId);
    if (!configuredType) {
      return this.applyQuestionType(typeId);
    }

    const configuredSettings = configuredType.settings || {};
    const configuredBuilderMode = configuredSettings.builderMode || configuredSettings.slideType || 'quiz';
    const configuredSlideType = configuredBuilderMode === 'media' ? 'media' : 'quiz';
    const configuredLayoutId = configuredSettings.layoutId || this.canvasPresetManager.getDefaultLayoutId(configuredSlideType);
    const interactionConfig = {
      ...this.getDefaultInteractionConfig(typeId, configuredSlideType),
      ...(configuredSettings.interaction || {}),
    };

    if (configuredSlideType === 'media') {
      if (configuredSettings.themeId) {
        const configuredTheme = this.getThemeById(configuredSettings.themeId);
        if (configuredTheme) {
          this.applyTheme(configuredTheme);
        }
      }

      slide.type = 'media';
      slide.originalType = typeId;
      slide.infoMode = true;
      slide.answers = [];
      slide.answerImages = [];
      slide.correctAnswerIndex = null;
      slide.correctAnswerIndexes = [];
      slide.selectType = 'single';
      slide.settings = slide.settings || {};
      slide.settings.builderMode = configuredBuilderMode;
      slide.settings.layoutId = configuredLayoutId;
      slide.settings.mediaLayout = slide.settings.mediaLayout || 'big-media';
      slide.settings.mediaTextPosition = slide.settings.mediaTextPosition || 'bottom';
      slide.settings.limits = {
        ...this.getDefaultQuestionLimits(),
        ...(configuredSettings.limits || {}),
      };
      slide.settings.timer = {
        ...(slide.settings.timer || {}),
        ...(configuredSettings.timer || {}),
      };
      slide.settings.scoring = {
        ...(slide.settings.scoring || {}),
        ...(configuredSettings.scoring || {}),
      };
      slide.settings.audio = {
        ...(slide.settings.audio || {}),
        ...(configuredSettings.audio || {}),
      };
      slide.settings.interaction = {
        ...(slide.settings.interaction || {}),
        ...interactionConfig,
      };
      slide.settings.textEntry = {
        ...this.getDefaultTextEntrySettings(),
        ...(slide.settings.textEntry || {}),
        ...(configuredSettings.textEntry || {}),
      };
      slide.settings.sliderConfig = {
        ...this.getDefaultSliderSettings(),
        ...(slide.settings.sliderConfig || {}),
        ...(configuredSettings.sliderConfig || {}),
        min: Number(configuredSettings.sliderConfig?.min ?? configuredSettings.sliderMin ?? slide.settings.sliderConfig?.min ?? 0),
        max: Number(configuredSettings.sliderConfig?.max ?? configuredSettings.sliderMax ?? slide.settings.sliderConfig?.max ?? 100),
        correct: Number(configuredSettings.sliderConfig?.correct ?? configuredSettings.sliderCorrect ?? slide.settings.sliderConfig?.correct ?? 50),
        step: Number(configuredSettings.sliderConfig?.step ?? slide.settings.sliderConfig?.step ?? 1),
      };
      slide.settings.hotspotConfig = {
        ...this.getDefaultHotspotSettings(),
        ...(slide.settings.hotspotConfig || {}),
        ...(configuredSettings.hotspotConfig || {}),
      };
      slide.settings.dragDropConfig = {
        ...this.getDefaultDragDropSettings(),
        ...(slide.settings.dragDropConfig || {}),
        ...(configuredSettings.dragDropConfig || {}),
      };
      slide.settings.matchPairsConfig = {
        ...this.getDefaultMatchPairsSettings(),
        ...(slide.settings.matchPairsConfig || {}),
        ...(configuredSettings.matchPairsConfig || {}),
      };
      slide.settings.customRuntimeState = {
        ...this.getDefaultCustomRuntimeState(),
        ...(slide.settings.customRuntimeState || {}),
      };
      slide.settings.interactionRuntime = {
        ...this.getDefaultInteractionRuntimeState(),
        ...(slide.settings.interactionRuntime || {}),
      };
      slide.meta = {
        ...(slide.meta || {}),
        questionTypeId: typeId,
        questionTypeName: configuredType.name || typeId,
        builderMode: configuredBuilderMode,
        themeId: configuredSettings.themeId || this.data.settings?.themeId || '',
      };

      return this.normalizeSlideStructure(slide);
    }

    const configuredAnswerCount = Number(
      interactionConfig.type === 'true_false'
        ? 2
        : (configuredSettings.answerCount || slide.settings?.answerPresentation?.answerCount || 4)
    );
    const normalizedAnswerCount = [2, 4, 6].includes(configuredAnswerCount) ? configuredAnswerCount : 4;

    if (configuredSettings.themeId) {
      const configuredTheme = this.getThemeById(configuredSettings.themeId);
      if (configuredTheme) {
        this.applyTheme(configuredTheme);
      }
    }

    slide.type = 'quiz';
    slide.originalType = typeId;
    slide.infoMode = false;
    slide.answers = this.createQuizAnswers(normalizedAnswerCount);
    slide.answerImages = Array.from({ length: normalizedAnswerCount }, () => '');
    slide.correctAnswerIndex = null;
    slide.correctAnswerIndexes = [];
    slide.selectType = interactionConfig.type === 'true_false' ? 'single' : (configuredSettings.allowMultiple ? 'multiple' : 'single');
    slide.settings = slide.settings || {};
    slide.settings.builderMode = configuredBuilderMode;
    slide.settings.layoutId = configuredLayoutId;
    const configuredLayout = this.canvasPresetManager.getLayoutById('quiz', configuredLayoutId, this.data?.settings?.lang || 'en');
    if (configuredLayout && Array.isArray(configuredLayout.supportsAnswers) && configuredLayout.supportsAnswers.length && !configuredLayout.supportsAnswers.includes(normalizedAnswerCount)) {
      slide.answers = this.createQuizAnswers(configuredLayout.supportsAnswers[0]);
      slide.answerImages = Array.from({ length: configuredLayout.supportsAnswers[0] }, () => '');
    }
    slide.settings.limits = {
      ...this.getDefaultQuestionLimits(),
      ...(configuredSettings.limits || {}),
    };
    slide.settings.timer = {
      ...(slide.settings.timer || {}),
      ...(configuredSettings.timer || {}),
    };
    slide.settings.scoring = {
      ...(slide.settings.scoring || {}),
      ...(configuredSettings.scoring || {}),
    };
    slide.settings.audio = {
      ...(slide.settings.audio || {}),
      ...(configuredSettings.audio || {}),
    };
    slide.settings.interaction = {
      ...(slide.settings.interaction || {}),
      ...interactionConfig,
    };
    slide.settings.textEntry = {
      ...this.getDefaultTextEntrySettings(),
      ...(slide.settings.textEntry || {}),
      ...(configuredSettings.textEntry || {}),
    };
    slide.settings.sliderConfig = {
      ...this.getDefaultSliderSettings(),
      ...(slide.settings.sliderConfig || {}),
      ...(configuredSettings.sliderConfig || {}),
      min: Number(configuredSettings.sliderConfig?.min ?? configuredSettings.sliderMin ?? slide.settings.sliderConfig?.min ?? 0),
      max: Number(configuredSettings.sliderConfig?.max ?? configuredSettings.sliderMax ?? slide.settings.sliderConfig?.max ?? 100),
      correct: Number(configuredSettings.sliderConfig?.correct ?? configuredSettings.sliderCorrect ?? slide.settings.sliderConfig?.correct ?? 50),
      step: Number(configuredSettings.sliderConfig?.step ?? slide.settings.sliderConfig?.step ?? 1),
    };
    slide.settings.hotspotConfig = {
      ...this.getDefaultHotspotSettings(),
      ...(slide.settings.hotspotConfig || {}),
      ...(configuredSettings.hotspotConfig || {}),
    };
    slide.settings.dragDropConfig = {
      ...this.getDefaultDragDropSettings(),
      ...(slide.settings.dragDropConfig || {}),
      ...(configuredSettings.dragDropConfig || {}),
    };
    slide.settings.matchPairsConfig = {
      ...this.getDefaultMatchPairsSettings(),
      ...(slide.settings.matchPairsConfig || {}),
      ...(configuredSettings.matchPairsConfig || {}),
    };
    slide.settings.customRuntimeState = {
      ...this.getDefaultCustomRuntimeState(),
      ...(slide.settings.customRuntimeState || {}),
    };
    slide.settings.interactionRuntime = {
      ...this.getDefaultInteractionRuntimeState(),
      ...(slide.settings.interactionRuntime || {}),
    };
    slide.settings.imageReveal = {
      ...(slide.settings.imageReveal || {}),
      ...(configuredSettings.imageReveal || {}),
    };
    slide.settings.answerPresentation = {
      ...this.getDefaultAnswerPresentationSettings(),
      ...(slide.settings.answerPresentation || {}),
      ...(configuredSettings.answerPresentation || {}),
      answerCount: Array.isArray(slide.answers) ? slide.answers.length : normalizedAnswerCount,
      layoutPreset: `${Array.isArray(slide.answers) ? slide.answers.length : normalizedAnswerCount}x${Array.isArray(slide.answers) ? slide.answers.length : normalizedAnswerCount}`,
      useMainMediaAsAnswerBackground: !!configuredSettings.answerPresentation?.useMainMediaAsAnswerBackground,
    };

    if (interactionConfig.type === 'true_false') {
      slide.answers = [
        this.normalizeAnswerObject(slide.answers[0] || this.createAnswerObject(0, 'Adevarat'), 0),
        this.normalizeAnswerObject(slide.answers[1] || this.createAnswerObject(1, 'Fals'), 1),
      ];
      slide.answers[0].text = slide.answers[0].text || 'Adevarat';
      slide.answers[1].text = slide.answers[1].text || 'Fals';
      slide.answerImages = ['', ''];
      slide.correctAnswerIndexes = [];
      slide.correctAnswerIndex = null;
    }

    if (interactionConfig.type === 'text_input' || interactionConfig.type === 'slider' || interactionConfig.type === 'hotspot') {
      slide.answers = [];
      slide.answerImages = [];
      slide.correctAnswerIndexes = [];
      slide.correctAnswerIndex = null;
      slide.selectType = 'single';
    }

    slide.meta = {
      ...(slide.meta || {}),
      questionTypeId: typeId,
      questionTypeName: configuredType.name || typeId,
      builderMode: configuredBuilderMode,
      themeId: configuredSettings.themeId || this.data.settings?.themeId || '',
    };

    return this.normalizeSlideStructure(slide);
  }
}
