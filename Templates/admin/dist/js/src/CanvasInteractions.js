import { PopupTemplates } from './PopupTemplates.js';

export class CanvasInteractions {
  constructor({ dom, store, canvasRenderer, slideRenderer, storage, popup }) {
    this.dom = dom;
    this.store = store;
    this.canvasRenderer = canvasRenderer;
    this.slideRenderer = slideRenderer;
    this.storage = storage;
    this.popup = popup;

    this.currentAnswerImageIndex = null;
    this.currentDraggedAnswerIndex = null;
  }

  rerenderAndSave(includeSlides = false) {
    this.canvasRenderer.render();
    if (includeSlides) {
      this.slideRenderer.renderSlides();
    }
    this.storage.save();
  }

  clampNumber(rawValue, fallbackValue, minimumValue = null, maximumValue = null) {
    const parsedValue = Number(rawValue);
    const normalizedValue = Number.isFinite(parsedValue) ? parsedValue : fallbackValue;

    if (minimumValue !== null && normalizedValue < minimumValue) {
      return minimumValue;
    }

    if (maximumValue !== null && normalizedValue > maximumValue) {
      return maximumValue;
    }

    return normalizedValue;
  }

  ensureInteractionSettings(currentSlideObject) {
    if (!currentSlideObject) return {};

    currentSlideObject.settings = currentSlideObject.settings || {};
    currentSlideObject.settings.interaction = {
      ...this.store.getDefaultInteractionConfig(
        currentSlideObject.originalType || currentSlideObject.type || 'quiz',
        currentSlideObject.type === 'media' ? 'media' : 'quiz'
      ),
      ...(currentSlideObject.settings.interaction || {})
    };

    return currentSlideObject.settings.interaction;
  }

  ensureTextEntrySettings(currentSlideObject) {
    if (!currentSlideObject) return {};

    currentSlideObject.settings = currentSlideObject.settings || {};
    currentSlideObject.settings.textEntry = {
      ...this.store.getDefaultTextEntrySettings(),
      ...(currentSlideObject.settings.textEntry || {})
    };

    return currentSlideObject.settings.textEntry;
  }

  ensureSliderSettings(currentSlideObject) {
    if (!currentSlideObject) return {};

    currentSlideObject.settings = currentSlideObject.settings || {};
    currentSlideObject.settings.sliderConfig = {
      ...this.store.getDefaultSliderSettings(),
      ...(currentSlideObject.settings.sliderConfig || {})
    };

    currentSlideObject.settings.sliderMin = currentSlideObject.settings.sliderConfig.min;
    currentSlideObject.settings.sliderMax = currentSlideObject.settings.sliderConfig.max;
    currentSlideObject.settings.sliderCorrect = currentSlideObject.settings.sliderConfig.correct;

    return currentSlideObject.settings.sliderConfig;
  }

  ensureHotspotSettings(currentSlideObject) {
    if (!currentSlideObject) return {};

    currentSlideObject.settings = currentSlideObject.settings || {};
    currentSlideObject.settings.hotspotConfig = {
      ...this.store.getDefaultHotspotSettings(),
      ...(currentSlideObject.settings.hotspotConfig || {})
    };

    return currentSlideObject.settings.hotspotConfig;
  }

  ensureDragDropSettings(currentSlideObject) {
    if (!currentSlideObject) return {};

    currentSlideObject.settings = currentSlideObject.settings || {};
    currentSlideObject.settings.dragDropConfig = {
      ...this.store.getDefaultDragDropSettings(),
      ...(currentSlideObject.settings.dragDropConfig || {})
    };

    currentSlideObject.settings.dragDropConfig.zoneCount = Math.max(
      2,
      Math.min(6, this.clampNumber(currentSlideObject.settings.dragDropConfig.zoneCount, 4))
    );
    currentSlideObject.settings.dragDropConfig.zoneLabels = this.store.ensureStringListLength(
      currentSlideObject.settings.dragDropConfig.zoneLabels,
      currentSlideObject.settings.dragDropConfig.zoneCount,
      'Zona'
    );
    currentSlideObject.settings.dragDropConfig.previewAssignments =
      typeof currentSlideObject.settings.dragDropConfig.previewAssignments === 'object' &&
      currentSlideObject.settings.dragDropConfig.previewAssignments !== null
        ? currentSlideObject.settings.dragDropConfig.previewAssignments
        : {};

    return currentSlideObject.settings.dragDropConfig;
  }

  ensureMatchPairsSettings(currentSlideObject) {
    if (!currentSlideObject) return {};

    currentSlideObject.settings = currentSlideObject.settings || {};
    currentSlideObject.settings.matchPairsConfig = {
      ...this.store.getDefaultMatchPairsSettings(),
      ...(currentSlideObject.settings.matchPairsConfig || {})
    };

    currentSlideObject.settings.matchPairsConfig.pairCount = Math.max(
      2,
      Math.min(6, this.clampNumber(currentSlideObject.settings.matchPairsConfig.pairCount, 4))
    );
    currentSlideObject.settings.matchPairsConfig.rightItems = this.store.ensureStringListLength(
      currentSlideObject.settings.matchPairsConfig.rightItems,
      currentSlideObject.settings.matchPairsConfig.pairCount,
      'Tinta'
    );
    currentSlideObject.settings.matchPairsConfig.previewPairs =
      typeof currentSlideObject.settings.matchPairsConfig.previewPairs === 'object' &&
      currentSlideObject.settings.matchPairsConfig.previewPairs !== null
        ? currentSlideObject.settings.matchPairsConfig.previewPairs
        : {};
    currentSlideObject.settings.matchPairsConfig.activeLeftIndex =
      Number.isInteger(currentSlideObject.settings.matchPairsConfig.activeLeftIndex)
        ? currentSlideObject.settings.matchPairsConfig.activeLeftIndex
        : null;

    return currentSlideObject.settings.matchPairsConfig;
  }

  ensureCustomRuntimeState(currentSlideObject) {
    if (!currentSlideObject) return {};

    currentSlideObject.settings = currentSlideObject.settings || {};
    currentSlideObject.settings.customRuntimeState = {
      ...this.store.getDefaultCustomRuntimeState(),
      ...(currentSlideObject.settings.customRuntimeState || {}),
      inputs:
        typeof currentSlideObject.settings.customRuntimeState?.inputs === 'object' &&
        currentSlideObject.settings.customRuntimeState.inputs !== null
          ? currentSlideObject.settings.customRuntimeState.inputs
          : {},
      buttons:
        typeof currentSlideObject.settings.customRuntimeState?.buttons === 'object' &&
        currentSlideObject.settings.customRuntimeState.buttons !== null
          ? currentSlideObject.settings.customRuntimeState.buttons
          : {},
    };

    return currentSlideObject.settings.customRuntimeState;
  }

  ensureInteractionRuntimeState(currentSlideObject) {
    return this.store.ensureInteractionRuntimeState(currentSlideObject);
  }

  resetInteractionRuntimeState(currentSlideObject) {
    this.store.resetInteractionRuntimeState(currentSlideObject);
  }

  maybeAutoEvaluate(currentSlideObject) {
    const interactionObject = this.ensureInteractionSettings(currentSlideObject);
    if (!interactionObject.autoEvaluate) return;
    this.store.evaluateInteractionForSlide(currentSlideObject);
  }

  bind() {
    document.addEventListener('click', this.handleClick);
    document.addEventListener('input', this.handleInput);
    document.addEventListener('change', this.handleChange);
    document.addEventListener('blur', this.handleBlur, true);
    document.addEventListener('dragstart', this.handleDragStart);
    document.addEventListener('dragover', this.handleDragOver);
    document.addEventListener('drop', this.handleDrop);
  }

  detectMediaType(url = '') {
    const normalizedValue = String(url).toLowerCase().trim();

    if (normalizedValue.includes('youtube.com/watch') || normalizedValue.includes('youtu.be/')) return 'youtube';
    if (normalizedValue.endsWith('.gif')) return 'gif';
    if (normalizedValue.endsWith('.mp4') || normalizedValue.endsWith('.webm') || normalizedValue.endsWith('.ogg')) return 'video';
    if (normalizedValue.endsWith('.mp3') || normalizedValue.endsWith('.wav') || normalizedValue.endsWith('.ogg') || normalizedValue.endsWith('.m4a')) return 'audio';
    return 'image';
  }

  youtubeEmbed(url = '') {
    const rawUrlString = String(url).trim();

    const shortMatchResult = rawUrlString.match(/youtu\.be\/([^?&]+)/);
    if (shortMatchResult?.[1]) {
      return `https://www.youtube.com/embed/${shortMatchResult[1]}`;
    }

    const normalMatchResult = rawUrlString.match(/[?&]v=([^&]+)/);
    if (normalMatchResult?.[1]) {
      return `https://www.youtube.com/embed/${normalMatchResult[1]}`;
    }

    return rawUrlString;
  }

  fileToDataUrl(fileObject) {
    return new Promise((resolvePromise, rejectPromise) => {
      const fileReaderObject = new FileReader();
      fileReaderObject.onload = () => resolvePromise(String(fileReaderObject.result || ''));
      fileReaderObject.onerror = rejectPromise;
      fileReaderObject.readAsDataURL(fileObject);
    });
  }

  applyMedia(type, url) {
    const currentSlideObject = this.store.getCurrentSlide();
    if (!currentSlideObject || !url) return;

    const finalMediaType = type === 'auto' ? this.detectMediaType(url) : type;
    const finalMediaUrl = finalMediaType === 'youtube' ? this.youtubeEmbed(url) : url;

    this.store.setSlideMedia({
      type: finalMediaType,
      url: finalMediaUrl
    });

    this.popup.close();
    this.canvasRenderer.render();
    this.slideRenderer.renderSlides();
    this.storage.save();
  }

  switchMediaTab(tabName) {
    const tabButtonNodeList = document.querySelectorAll('.media-tab');
    const tabPanelNodeList = document.querySelectorAll('.media-tab-panel');

    tabButtonNodeList.forEach((tabButtonElement) => {
      tabButtonElement.classList.toggle('is-active', tabButtonElement.dataset.tab === tabName);
    });

    tabPanelNodeList.forEach((tabPanelElement) => {
      tabPanelElement.classList.toggle('is-active', tabPanelElement.dataset.panel === tabName);
    });
  }

  applyAudioValue(target, value) {
    const currentSlideObject = this.store.getCurrentSlide();
    if (!currentSlideObject) return;

    currentSlideObject.settings = currentSlideObject.settings || {};
    currentSlideObject.settings.audio = currentSlideObject.settings.audio || {};

    if (target === 'music') {
      this.store.getData().settings.musicUrl = value || '';
    }

    if (target === 'correct') {
      currentSlideObject.settings.audio.correctSoundUrl = value || '';
    }

    if (target === 'wrong') {
      currentSlideObject.settings.audio.wrongSoundUrl = value || '';
    }

    if (target === 'gongStart') {
      currentSlideObject.settings.audio.gongStartUrl = value || '';
    }

    if (target === 'gongEnd') {
      currentSlideObject.settings.audio.gongEndUrl = value || '';
    }

    this.canvasRenderer.render();
    this.storage.save();
  }

  ensureAnswerObjectShape(currentSlideObject, answerIndexNumber) {
    if (!currentSlideObject || !Array.isArray(currentSlideObject.answers)) return null;

    const rawAnswerValue = currentSlideObject.answers[answerIndexNumber];
    if (rawAnswerValue === undefined) return null;

    if (typeof rawAnswerValue === 'object' && rawAnswerValue !== null) {
      rawAnswerValue.text = rawAnswerValue.text || '';
      rawAnswerValue.correct = !!rawAnswerValue.correct;
      rawAnswerValue.color = rawAnswerValue.color || this.getColorByIndex(answerIndexNumber);
      rawAnswerValue.imageUrl = rawAnswerValue.imageUrl || '';
      return rawAnswerValue;
    }

    const normalizedAnswerObject = {
      text: String(rawAnswerValue || ''),
      correct: false,
      color: this.getColorByIndex(answerIndexNumber),
      imageUrl: ''
    };

    currentSlideObject.answers[answerIndexNumber] = normalizedAnswerObject;
    return normalizedAnswerObject;
  }

  getColorByIndex(answerIndexNumber) {
    const colorList = ['purple', 'orange', 'green', 'yellow', 'blue', 'red'];
    return colorList[answerIndexNumber % colorList.length];
  }

  resizeAnswerArray(currentSlideObject, requiredAnswerCountNumber) {
    if (!currentSlideObject) return;

    const currentAnswersArray = Array.isArray(currentSlideObject.answers) ? currentSlideObject.answers : [];
    const newAnswersArray = [];

    for (let answerIndexNumber = 0; answerIndexNumber < requiredAnswerCountNumber; answerIndexNumber += 1) {
      const existingAnswerObject = currentAnswersArray[answerIndexNumber];

      if (typeof existingAnswerObject === 'object' && existingAnswerObject !== null) {
        newAnswersArray.push({
          text: existingAnswerObject.text || '',
          correct: !!existingAnswerObject.correct,
          color: existingAnswerObject.color || this.getColorByIndex(answerIndexNumber),
          imageUrl: existingAnswerObject.imageUrl || ''
        });
      } else {
        newAnswersArray.push({
          text: existingAnswerObject || '',
          correct: false,
          color: this.getColorByIndex(answerIndexNumber),
          imageUrl: ''
        });
      }
    }

    currentSlideObject.answers = newAnswersArray;

    if (currentSlideObject.selectType !== 'multiple') {
      const firstCorrectIndexNumber = newAnswersArray.findIndex((answerObject) => answerObject.correct);

      newAnswersArray.forEach((answerObject, answerIndexNumber) => {
        answerObject.correct = answerIndexNumber === firstCorrectIndexNumber;
      });

      currentSlideObject.correctAnswerIndex = firstCorrectIndexNumber >= 0 ? firstCorrectIndexNumber : null;
    } else {
      currentSlideObject.correctAnswerIndexes = newAnswersArray
          .map((answerObject, answerIndexNumber) => answerObject.correct ? answerIndexNumber : null)
          .filter((answerIndexNumber) => answerIndexNumber !== null);
    }
  }

  toggleCorrectAnswer(currentSlideObject, answerIndexNumber) {
    if (!currentSlideObject) return;

    const currentAnswerObject = this.ensureAnswerObjectShape(currentSlideObject, answerIndexNumber);
    if (!currentAnswerObject) return;

    if (currentSlideObject.selectType === 'multiple') {
      currentAnswerObject.correct = !currentAnswerObject.correct;

      currentSlideObject.correctAnswerIndexes = currentSlideObject.answers
          .map((answerObject, currentIndexNumber) => {
            const normalizedAnswerObject = this.ensureAnswerObjectShape(currentSlideObject, currentIndexNumber);
            return normalizedAnswerObject?.correct ? currentIndexNumber : null;
          })
          .filter((currentIndexNumber) => currentIndexNumber !== null);

      currentSlideObject.correctAnswerIndex = currentSlideObject.correctAnswerIndexes[0] ?? null;
      return;
    }

    currentSlideObject.answers.forEach((answerObject, currentIndexNumber) => {
      const normalizedAnswerObject = this.ensureAnswerObjectShape(currentSlideObject, currentIndexNumber);
      if (!normalizedAnswerObject) return;
      normalizedAnswerObject.correct = currentIndexNumber === answerIndexNumber;
    });

    currentSlideObject.correctAnswerIndex = answerIndexNumber;
    currentSlideObject.correctAnswerIndexes = [answerIndexNumber];
  }

  handleClick = async (eventObject) => {
    const currentSlideObject = this.store.getCurrentSlide();
    if (!currentSlideObject) return;

    const clickedActionElement = eventObject.target.closest('[data-action]');
    const actionName = clickedActionElement?.dataset?.action || null;

    const mediaCenterElement = eventObject.target.closest('#media-center');
    if (mediaCenterElement) {
      const mediaLibraryItems = await this.store.getImageLibraryCatalog();
      const mediaPopupTemplateObject = PopupTemplates.mediaLibraryAdvanced(
        mediaLibraryItems,
        this.store.getAudioLibrary()
      );

      this.currentAnswerImageIndex = null;

      this.popup.open({
        title: mediaPopupTemplateObject.title,
        content: mediaPopupTemplateObject.content,
        footerButtons: mediaPopupTemplateObject.footerButtons
      });
      return;
    }

    const switchTabButtonElement = eventObject.target.closest('[data-action="switch-media-tab"]');
    if (switchTabButtonElement) {
      const targetTabName = switchTabButtonElement.dataset.tab;
      if (!targetTabName) return;
      this.switchMediaTab(targetTabName);
      return;
    }

    if (actionName === 'open-answer-image') {
      const answerIndexNumber = Number(clickedActionElement.dataset.index);
      if (!Number.isFinite(answerIndexNumber)) return;

      this.currentAnswerImageIndex = answerIndexNumber;

      const mediaLibraryItems = await this.store.getImageLibraryCatalog();
      const imagePopupTemplateObject = PopupTemplates.mediaLibrary(mediaLibraryItems);

      this.popup.open({
        title: 'Alege imagine pentru răspuns',
        content: imagePopupTemplateObject.content,
        footerButtons: imagePopupTemplateObject.footerButtons
      });
      return;
    }
    if (actionName === 'remove-answer-image') {
      const answerIndexNumber = Number(clickedActionElement.dataset.index);
      if (!Number.isFinite(answerIndexNumber)) return;

      const answerObject = this.ensureAnswerObjectShape(currentSlideObject, answerIndexNumber);
      if (!answerObject) return;

      answerObject.imageUrl = '';

      this.canvasRenderer.render();
      this.slideRenderer.renderSlides();
      this.storage.save();
      return;
    }
    const libraryMediaElement = eventObject.target.closest('[data-action="select-library-media"]');
    if (libraryMediaElement) {
      const selectedMediaType = libraryMediaElement.dataset.mediaType || 'image';
      const selectedMediaUrl = libraryMediaElement.dataset.mediaUrl || '';
      this.applyMedia(selectedMediaType, selectedMediaUrl);
      return;
    }

    const selectedLibraryImageElement = eventObject.target.closest('[data-action="select-library-image"]');
    if (selectedLibraryImageElement) {
      const selectedImageUrl = selectedLibraryImageElement.dataset.imageUrl || '';
      if (!selectedImageUrl) return;

      if (this.currentAnswerImageIndex !== null) {
        const targetAnswerObject = this.ensureAnswerObjectShape(currentSlideObject, this.currentAnswerImageIndex);
        if (targetAnswerObject) {
          targetAnswerObject.imageUrl = selectedImageUrl;
        }

        this.currentAnswerImageIndex = null;
        this.popup.close();
        this.canvasRenderer.render();
        this.slideRenderer.renderSlides();
        this.storage.save();
        return;
      }

      this.applyMedia('image', selectedImageUrl);
      return;
    }

    const manualMediaApplyButton = eventObject.target.closest('[data-action="apply-manual-media-url"]');
    if (manualMediaApplyButton) {
      const mediaUrlInputElement = document.getElementById('manual-media-url');
      const mediaTypeSelectElement = document.getElementById('manual-media-type');

      const mediaUrlString = mediaUrlInputElement?.value?.trim() || '';
      const mediaTypeString = mediaTypeSelectElement?.value || 'image';

      if (!mediaUrlString) return;
      this.applyMedia(mediaTypeString, mediaUrlString);
      return;
    }

    const audioApplyButton = eventObject.target.closest('[data-action="apply-manual-audio-url"]');
    if (audioApplyButton) {
      const audioUrlInputElement = document.getElementById('manual-audio-url');
      const audioUrlString = audioUrlInputElement?.value?.trim() || '';
      if (!audioUrlString) return;

      this.applyMedia('audio', audioUrlString);
      return;
    }

    const uploadMediaButton = eventObject.target.closest('[data-action="upload-media-file"]');
    if (uploadMediaButton) {
      const uploadInputElement = document.getElementById('upload-media-file');
      const selectedFileObject = uploadInputElement?.files?.[0];
      if (!selectedFileObject) return;

      const mediaDataUrlString = await this.fileToDataUrl(selectedFileObject);

      let uploadedMediaType = 'audio';
      if (selectedFileObject.type.startsWith('video/')) uploadedMediaType = 'video';
      if (selectedFileObject.type.startsWith('audio/')) uploadedMediaType = 'audio';

      this.applyMedia(uploadedMediaType, mediaDataUrlString);
      return;
    }

    const correctAnswerButtonElement = eventObject.target.closest('[data-answer-correct]');
    if (correctAnswerButtonElement) {
      const answerIndexNumber = Number(correctAnswerButtonElement.dataset.answerCorrect);
      if (!Number.isFinite(answerIndexNumber)) return;

      this.toggleCorrectAnswer(currentSlideObject, answerIndexNumber);

      this.canvasRenderer.render();
      this.slideRenderer.renderSlides();
      this.storage.save();
      return;
    }

    const hotspotSurfaceElement = eventObject.target.closest('[data-hotspot-surface]');
    if (hotspotSurfaceElement) {
      const interactionRuntimeObject = this.ensureInteractionRuntimeState(currentSlideObject);
      const surfaceRectObject = hotspotSurfaceElement.getBoundingClientRect();

      if (!surfaceRectObject.width || !surfaceRectObject.height) return;

      const relativeX = ((eventObject.clientX - surfaceRectObject.left) / surfaceRectObject.width) * 100;
      const relativeY = ((eventObject.clientY - surfaceRectObject.top) / surfaceRectObject.height) * 100;

      interactionRuntimeObject.hotspotSelection = {
        x: this.clampNumber(relativeX, 50, 0, 100),
        y: this.clampNumber(relativeY, 50, 0, 100),
      };

      this.maybeAutoEvaluate(currentSlideObject);
      this.rerenderAndSave(true);
      return;
    }

    const previewAnswerSelectElement = eventObject.target.closest('[data-preview-answer-select]');
    if (previewAnswerSelectElement) {
      const answerIndexNumber = Number(previewAnswerSelectElement.dataset.previewAnswerSelect);
      if (!Number.isFinite(answerIndexNumber)) return;

      const interactionRuntimeObject = this.ensureInteractionRuntimeState(currentSlideObject);
      const isMultipleSelection = currentSlideObject.selectType === 'multiple';

      if (isMultipleSelection) {
        const selectedSet = new Set(Array.isArray(interactionRuntimeObject.selectedAnswers) ? interactionRuntimeObject.selectedAnswers : []);
        if (selectedSet.has(answerIndexNumber)) {
          selectedSet.delete(answerIndexNumber);
        } else {
          selectedSet.add(answerIndexNumber);
        }
        interactionRuntimeObject.selectedAnswers = Array.from(selectedSet).sort((firstIndex, secondIndex) => firstIndex - secondIndex);
      } else {
        interactionRuntimeObject.selectedAnswers = [answerIndexNumber];
      }

      this.maybeAutoEvaluate(currentSlideObject);
      this.rerenderAndSave();
      return;
    }

    const evaluateInteractionElement = eventObject.target.closest('[data-action="evaluate-interaction"]');
    if (evaluateInteractionElement) {
      this.store.evaluateInteractionForSlide(currentSlideObject);
      this.rerenderAndSave();
      return;
    }

    const resetInteractionElement = eventObject.target.closest('[data-action="reset-interaction-runtime"]');
    if (resetInteractionElement) {
      this.resetInteractionRuntimeState(currentSlideObject);
      this.rerenderAndSave();
      return;
    }

    const clearDropZoneElement = eventObject.target.closest('[data-clear-drop-zone]');
    if (clearDropZoneElement) {
      const zoneIndexNumber = Number(clearDropZoneElement.dataset.clearDropZone);
      if (!Number.isFinite(zoneIndexNumber)) return;

      const dragDropConfigObject = this.ensureDragDropSettings(currentSlideObject);
      delete dragDropConfigObject.previewAssignments[String(zoneIndexNumber)];
      this.rerenderAndSave();
      return;
    }

    const matchLeftElement = eventObject.target.closest('[data-match-left-index]');
    if (matchLeftElement) {
      const leftIndexNumber = Number(matchLeftElement.dataset.matchLeftIndex);
      if (!Number.isFinite(leftIndexNumber)) return;

      const matchPairsConfigObject = this.ensureMatchPairsSettings(currentSlideObject);
      matchPairsConfigObject.activeLeftIndex = leftIndexNumber;
      this.rerenderAndSave();
      return;
    }

    const matchRightElement = eventObject.target.closest('[data-match-right-index]');
    if (matchRightElement) {
      const rightIndexNumber = Number(matchRightElement.dataset.matchRightIndex);
      if (!Number.isFinite(rightIndexNumber)) return;

      const matchPairsConfigObject = this.ensureMatchPairsSettings(currentSlideObject);
      const activeLeftIndex = matchPairsConfigObject.activeLeftIndex;
      if (!Number.isInteger(activeLeftIndex)) return;

      Object.keys(matchPairsConfigObject.previewPairs).forEach((leftIndexKey) => {
        if (Number(matchPairsConfigObject.previewPairs[leftIndexKey]) === rightIndexNumber) {
          delete matchPairsConfigObject.previewPairs[leftIndexKey];
        }
      });

      matchPairsConfigObject.previewPairs[String(activeLeftIndex)] = rightIndexNumber;
      matchPairsConfigObject.activeLeftIndex = null;
      this.rerenderAndSave();
      return;
    }

    const clearMatchPairsElement = eventObject.target.closest('[data-clear-match-pairs]');
    if (clearMatchPairsElement) {
      const matchPairsConfigObject = this.ensureMatchPairsSettings(currentSlideObject);
      matchPairsConfigObject.previewPairs = {};
      matchPairsConfigObject.activeLeftIndex = null;
      this.rerenderAndSave();
      return;
    }

    const customBlockButtonElement = eventObject.target.closest('[data-custom-block-button]');
    if (customBlockButtonElement) {
      const blockId = customBlockButtonElement.dataset.customBlockButton || '';
      if (!blockId) return;

      const customRuntimeStateObject = this.ensureCustomRuntimeState(currentSlideObject);
      customRuntimeStateObject.buttons[blockId] = !customRuntimeStateObject.buttons[blockId];
      customRuntimeStateObject.lastAction = `button:${blockId}`;
      this.maybeAutoEvaluate(currentSlideObject);
      this.rerenderAndSave();
    }
  };

  handleInput = (eventObject) => {
    const currentSlideObject = this.store.getCurrentSlide();
    if (!currentSlideObject) return;

    if (eventObject.target.id === 'question-title') {
      currentSlideObject.title = eventObject.target.value;
      this.slideRenderer.renderSlides();
      this.storage.save();
      return;
    }

    const answerIndexFromDataset = eventObject.target.dataset.answerInput;
    if (answerIndexFromDataset !== undefined) {
      const answerIndexNumber = Number(answerIndexFromDataset);
      if (!Number.isFinite(answerIndexNumber)) return;

      const targetAnswerObject = this.ensureAnswerObjectShape(currentSlideObject, answerIndexNumber);
      if (!targetAnswerObject) return;

      targetAnswerObject.text = eventObject.target.value;

      this.slideRenderer.renderSlides();
      this.storage.save();
      return;
    }

    if (eventObject.target.dataset.slideDescription !== undefined) {
      currentSlideObject.description = eventObject.target.value;
      this.storage.save();
      return;
    }

    if (eventObject.target.dataset.mediaLinkUrl !== undefined) {
      currentSlideObject.linkUrl = eventObject.target.value;
      this.storage.save();
      return;
    }

    if (eventObject.target.dataset.mediaLinkLabel !== undefined) {
      currentSlideObject.linkLabel = eventObject.target.value;
      this.storage.save();
      return;
    }

    if (eventObject.target.dataset.textPlaceholder !== undefined) {
      const textEntryObject = this.ensureTextEntrySettings(currentSlideObject);
      textEntryObject.placeholder = eventObject.target.value || '';
      this.rerenderAndSave();
      return;
    }

    if (eventObject.target.dataset.textAccepted !== undefined) {
      const textEntryObject = this.ensureTextEntrySettings(currentSlideObject);
      textEntryObject.acceptedAnswers = String(eventObject.target.value || '')
        .split(',')
        .map((item) => item.trim())
        .filter(Boolean);
      this.rerenderAndSave();
      return;
    }

    if (eventObject.target.dataset.sliderMin !== undefined) {
      const sliderConfigObject = this.ensureSliderSettings(currentSlideObject);
      sliderConfigObject.min = this.clampNumber(eventObject.target.value, sliderConfigObject.min ?? 0);
      if (sliderConfigObject.max < sliderConfigObject.min) sliderConfigObject.max = sliderConfigObject.min;
      if (sliderConfigObject.correct < sliderConfigObject.min) sliderConfigObject.correct = sliderConfigObject.min;
      currentSlideObject.settings.sliderMin = sliderConfigObject.min;
      currentSlideObject.settings.sliderMax = sliderConfigObject.max;
      currentSlideObject.settings.sliderCorrect = sliderConfigObject.correct;
      this.rerenderAndSave();
      return;
    }

    if (eventObject.target.dataset.sliderMax !== undefined) {
      const sliderConfigObject = this.ensureSliderSettings(currentSlideObject);
      sliderConfigObject.max = this.clampNumber(eventObject.target.value, sliderConfigObject.max ?? 100);
      if (sliderConfigObject.max < sliderConfigObject.min) sliderConfigObject.min = sliderConfigObject.max;
      if (sliderConfigObject.correct > sliderConfigObject.max) sliderConfigObject.correct = sliderConfigObject.max;
      currentSlideObject.settings.sliderMin = sliderConfigObject.min;
      currentSlideObject.settings.sliderMax = sliderConfigObject.max;
      currentSlideObject.settings.sliderCorrect = sliderConfigObject.correct;
      this.rerenderAndSave();
      return;
    }

    if (eventObject.target.dataset.sliderCorrect !== undefined) {
      const sliderConfigObject = this.ensureSliderSettings(currentSlideObject);
      sliderConfigObject.correct = this.clampNumber(
        eventObject.target.value,
        sliderConfigObject.correct ?? 50,
        sliderConfigObject.min,
        sliderConfigObject.max
      );
      currentSlideObject.settings.sliderCorrect = sliderConfigObject.correct;
      this.rerenderAndSave();
      return;
    }

    if (eventObject.target.dataset.sliderStep !== undefined) {
      const sliderConfigObject = this.ensureSliderSettings(currentSlideObject);
      sliderConfigObject.step = Math.max(1, this.clampNumber(eventObject.target.value, sliderConfigObject.step ?? 1));
      this.rerenderAndSave();
      return;
    }

    if (eventObject.target.dataset.hotspotX !== undefined) {
      const hotspotConfigObject = this.ensureHotspotSettings(currentSlideObject);
      hotspotConfigObject.x = this.clampNumber(eventObject.target.value, hotspotConfigObject.x ?? 50, 0, 100);
      this.rerenderAndSave();
      return;
    }

    if (eventObject.target.dataset.hotspotY !== undefined) {
      const hotspotConfigObject = this.ensureHotspotSettings(currentSlideObject);
      hotspotConfigObject.y = this.clampNumber(eventObject.target.value, hotspotConfigObject.y ?? 50, 0, 100);
      this.rerenderAndSave();
      return;
    }

    if (eventObject.target.dataset.hotspotTolerance !== undefined) {
      const hotspotConfigObject = this.ensureHotspotSettings(currentSlideObject);
      hotspotConfigObject.tolerance = this.clampNumber(eventObject.target.value, hotspotConfigObject.tolerance ?? 12, 1, 50);
      this.rerenderAndSave();
      return;
    }

    if (eventObject.target.dataset.dragDropZoneCount !== undefined) {
      const dragDropConfigObject = this.ensureDragDropSettings(currentSlideObject);
      dragDropConfigObject.zoneCount = Math.max(2, Math.min(6, this.clampNumber(eventObject.target.value, dragDropConfigObject.zoneCount ?? 4)));
      dragDropConfigObject.zoneLabels = this.store.ensureStringListLength(
        dragDropConfigObject.zoneLabels,
        dragDropConfigObject.zoneCount,
        'Zona'
      );
      this.resizeAnswerArray(currentSlideObject, dragDropConfigObject.zoneCount);
      this.rerenderAndSave(true);
      return;
    }

    if (eventObject.target.dataset.dragDropZoneLabels !== undefined) {
      const dragDropConfigObject = this.ensureDragDropSettings(currentSlideObject);
      const newLabelsList = String(eventObject.target.value || '')
        .split(',')
        .map((item) => item.trim())
        .filter(Boolean);
      dragDropConfigObject.zoneLabels = this.store.ensureStringListLength(newLabelsList, dragDropConfigObject.zoneCount, 'Zona');
      this.rerenderAndSave();
      return;
    }

    if (eventObject.target.dataset.matchPairsCount !== undefined) {
      const matchPairsConfigObject = this.ensureMatchPairsSettings(currentSlideObject);
      matchPairsConfigObject.pairCount = Math.max(2, Math.min(6, this.clampNumber(eventObject.target.value, matchPairsConfigObject.pairCount ?? 4)));
      matchPairsConfigObject.rightItems = this.store.ensureStringListLength(
        matchPairsConfigObject.rightItems,
        matchPairsConfigObject.pairCount,
        'Tinta'
      );
      this.resizeAnswerArray(currentSlideObject, matchPairsConfigObject.pairCount);
      this.rerenderAndSave(true);
      return;
    }

    if (eventObject.target.dataset.matchRightItems !== undefined) {
      const matchPairsConfigObject = this.ensureMatchPairsSettings(currentSlideObject);
      const newItemsList = String(eventObject.target.value || '')
        .split(',')
        .map((item) => item.trim())
        .filter(Boolean);
      matchPairsConfigObject.rightItems = this.store.ensureStringListLength(newItemsList, matchPairsConfigObject.pairCount, 'Tinta');
      this.rerenderAndSave();
      return;
    }

    if (eventObject.target.dataset.runtimeTextInput !== undefined) {
      const interactionRuntimeObject = this.ensureInteractionRuntimeState(currentSlideObject);
      interactionRuntimeObject.textValue = eventObject.target.value || '';
      this.maybeAutoEvaluate(currentSlideObject);
      this.storage.save();
      return;
    }

    if (eventObject.target.dataset.runtimeSlider !== undefined) {
      const interactionRuntimeObject = this.ensureInteractionRuntimeState(currentSlideObject);
      interactionRuntimeObject.sliderValue = Number(eventObject.target.value || 0);
      this.maybeAutoEvaluate(currentSlideObject);
      this.storage.save();
      const sliderValueElement = document.querySelector('[data-preview-slider-value]');
      if (sliderValueElement) {
        sliderValueElement.textContent = String(eventObject.target.value || '0');
      }
      return;
    }

    if (eventObject.target.dataset.customBlockInput !== undefined) {
      const customRuntimeStateObject = this.ensureCustomRuntimeState(currentSlideObject);
      const blockId = eventObject.target.dataset.customBlockInput || '';
      if (!blockId) return;
      customRuntimeStateObject.inputs[blockId] = eventObject.target.value || '';
      customRuntimeStateObject.lastAction = `input:${blockId}`;
      this.maybeAutoEvaluate(currentSlideObject);
      this.storage.save();
      return;
    }

    if (eventObject.target.dataset.previewSlider !== undefined) {
      const sliderValueElement = document.querySelector('[data-preview-slider-value]');
      if (sliderValueElement) {
        sliderValueElement.textContent = String(eventObject.target.value || '0');
      }
    }
  };

  handleChange = async (eventObject) => {
    const currentSlideObject = this.store.getCurrentSlide();
    if (!currentSlideObject) return;

    if (eventObject.target.dataset.mediaLayout !== undefined) {
      currentSlideObject.settings = currentSlideObject.settings || {};
      currentSlideObject.settings.mediaLayout = eventObject.target.value;
      this.canvasRenderer.render();
      this.storage.save();
      return;
    }

    if (eventObject.target.dataset.mediaTextPosition !== undefined) {
      currentSlideObject.settings = currentSlideObject.settings || {};
      currentSlideObject.settings.mediaTextPosition = eventObject.target.value;
      this.canvasRenderer.render();
      this.storage.save();
      return;
    }

    if (eventObject.target.dataset.gameMode !== undefined) {
      this.store.getData().settings.mode = eventObject.target.value;
      this.storage.save();
      return;
    }

    if (eventObject.target.dataset.layoutSelect !== undefined) {
      this.store.setLayoutForCurrentSlide(eventObject.target.value || '');
      this.canvasRenderer.render();
      this.slideRenderer.renderSlides();
      this.storage.save();
      return;
    }

    if (eventObject.target.dataset.answerCountSelect !== undefined) {
      const requiredAnswerCountNumber = Number(eventObject.target.value || 4);
      if (![2, 4, 6].includes(requiredAnswerCountNumber)) return;

      this.resizeAnswerArray(currentSlideObject, requiredAnswerCountNumber);
      this.canvasRenderer.render();
      this.slideRenderer.renderSlides();
      this.storage.save();
      return;
    }

    if (eventObject.target.id === 'select-multiple') {
      const questionTypeConfigurationObject = this.store.getQuestionTypeConfig(currentSlideObject.originalType || currentSlideObject.type);
      const allowMultipleAnswers = !!questionTypeConfigurationObject?.settings?.allowMultiple;

      if (!allowMultipleAnswers) {
        eventObject.target.checked = false;
        currentSlideObject.selectType = 'single';
      } else {
        currentSlideObject.selectType = eventObject.target.checked ? 'multiple' : 'single';
      }

      if (currentSlideObject.selectType !== 'multiple') {
        let firstCorrectIndexNumber = null;

        currentSlideObject.answers.forEach((answerObjectOrString, answerIndexNumber) => {
          const normalizedAnswerObject = this.ensureAnswerObjectShape(currentSlideObject, answerIndexNumber);
          if (!normalizedAnswerObject) return;

          if (normalizedAnswerObject.correct && firstCorrectIndexNumber === null) {
            firstCorrectIndexNumber = answerIndexNumber;
          }
        });

        currentSlideObject.answers.forEach((answerObjectOrString, answerIndexNumber) => {
          const normalizedAnswerObject = this.ensureAnswerObjectShape(currentSlideObject, answerIndexNumber);
          if (!normalizedAnswerObject) return;
          normalizedAnswerObject.correct = answerIndexNumber === firstCorrectIndexNumber;
        });

        currentSlideObject.correctAnswerIndex = firstCorrectIndexNumber;
        currentSlideObject.correctAnswerIndexes = firstCorrectIndexNumber !== null ? [firstCorrectIndexNumber] : [];
      } else {
        currentSlideObject.correctAnswerIndexes = currentSlideObject.answers
            .map((answerObjectOrString, answerIndexNumber) => {
              const normalizedAnswerObject = this.ensureAnswerObjectShape(currentSlideObject, answerIndexNumber);
              return normalizedAnswerObject?.correct ? answerIndexNumber : null;
            })
            .filter((answerIndexNumber) => answerIndexNumber !== null);

        currentSlideObject.correctAnswerIndex = currentSlideObject.correctAnswerIndexes[0] ?? null;
      }

      this.canvasRenderer.render();
      this.storage.save();
      return;
    }

    if (eventObject.target.matches('input[type="radio"][name="time"]')) {
      currentSlideObject.settings = currentSlideObject.settings || {};
      currentSlideObject.settings.timer = currentSlideObject.settings.timer || {};
      currentSlideObject.settings.timer.selected = eventObject.target.value;
      this.store.getData().settings.timeLimit = eventObject.target.value;
      this.storage.save();
      return;
    }

    if (eventObject.target.id === 'bonus-toggle') {
      currentSlideObject.settings = currentSlideObject.settings || {};
      currentSlideObject.settings.scoring = currentSlideObject.settings.scoring || {};
      currentSlideObject.settings.scoring.speedBonus = currentSlideObject.settings.scoring.speedBonus || {};
      currentSlideObject.settings.scoring.speedBonus.enabled = !!eventObject.target.checked;
      this.store.getData().settings.bonusSpeed = !!eventObject.target.checked;
      this.canvasRenderer.render();
      this.storage.save();
      return;
    }

    if (eventObject.target.id === 'bonus-range') {
      const selectedSecondsValue = Number(eventObject.target.value || 10);

      currentSlideObject.settings = currentSlideObject.settings || {};
      currentSlideObject.settings.scoring = currentSlideObject.settings.scoring || {};
      currentSlideObject.settings.scoring.speedBonus = currentSlideObject.settings.scoring.speedBonus || {};
      currentSlideObject.settings.scoring.speedBonus.withinSeconds = selectedSecondsValue;
      this.store.getData().settings.bonusTime = selectedSecondsValue;
      this.canvasRenderer.render();
      this.storage.save();
      return;
    }

    if (eventObject.target.dataset.audioSelect !== undefined) {
      const targetAudioType = eventObject.target.dataset.audioSelect;
      this.applyAudioValue(targetAudioType, eventObject.target.value || '');
      return;
    }

    if (eventObject.target.dataset.audioUpload !== undefined) {
      const targetAudioType = eventObject.target.dataset.audioUpload;
      const selectedAudioFileObject = eventObject.target.files?.[0];
      if (!selectedAudioFileObject) return;

      const uploadedAudioDataUrl = await this.fileToDataUrl(selectedAudioFileObject);
      this.applyAudioValue(targetAudioType, uploadedAudioDataUrl);
      return;
    }
  };

  handleBlur = (eventObject) => {
    const currentSlideObject = this.store.getCurrentSlide();
    if (!currentSlideObject) return;

    if (
        eventObject.target.id === 'question-title' ||
        eventObject.target.dataset.slideDescription !== undefined ||
        eventObject.target.dataset.mediaLinkUrl !== undefined ||
        eventObject.target.dataset.mediaLinkLabel !== undefined
    ) {
      this.canvasRenderer.render();
      this.slideRenderer.renderSlides();
      this.storage.save();
    }
  };

  handleDragStart = (eventObject) => {
    const dragSourceElement = eventObject.target.closest('[data-drag-source-index]');
    if (!dragSourceElement) return;

    const sourceIndexNumber = Number(dragSourceElement.dataset.dragSourceIndex);
    if (!Number.isFinite(sourceIndexNumber)) return;

    this.currentDraggedAnswerIndex = sourceIndexNumber;
    if (eventObject.dataTransfer) {
      eventObject.dataTransfer.effectAllowed = 'move';
      eventObject.dataTransfer.setData('text/plain', String(sourceIndexNumber));
    }
  };

  handleDragOver = (eventObject) => {
    const dropZoneElement = eventObject.target.closest('[data-drop-zone-index]');
    if (!dropZoneElement) return;
    eventObject.preventDefault();
    if (eventObject.dataTransfer) {
      eventObject.dataTransfer.dropEffect = 'move';
    }
  };

  handleDrop = (eventObject) => {
    const currentSlideObject = this.store.getCurrentSlide();
    if (!currentSlideObject) return;

    const dropZoneElement = eventObject.target.closest('[data-drop-zone-index]');
    if (!dropZoneElement) return;
    eventObject.preventDefault();

    const zoneIndexNumber = Number(dropZoneElement.dataset.dropZoneIndex);
    const droppedAnswerIndex = Number(
      eventObject.dataTransfer?.getData('text/plain') || this.currentDraggedAnswerIndex
    );

    if (!Number.isFinite(zoneIndexNumber) || !Number.isFinite(droppedAnswerIndex)) return;

    const dragDropConfigObject = this.ensureDragDropSettings(currentSlideObject);
    dragDropConfigObject.previewAssignments[String(zoneIndexNumber)] = droppedAnswerIndex;
    this.currentDraggedAnswerIndex = null;
    this.rerenderAndSave();
  };
}
