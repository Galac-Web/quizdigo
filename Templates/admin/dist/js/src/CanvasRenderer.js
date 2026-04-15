import { COLORS } from './constants.js';

export class CanvasRenderer {
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

  getQuizSettingRow() {
    return this.dom.selectMultiple?.closest('div[style]') || null;
  }

  getTimeTitle() {
    return this.dom.timeGrid?.previousElementSibling || null;
  }

  getBonusTitle() {
    return this.dom.bonusToggle?.closest('.bonus-row')?.previousElementSibling || null;
  }

  getTitleBox() {
    return this.dom.questionTitle?.closest('.title-box') || null;
  }

  getCanvasPreset(slideObject) {
    return this.store.getResolvedLayoutForSlide(slideObject) || {};
  }

  getThemePreset() {
    return this.store.getActiveThemePreset() || { tokens: {} };
  }

  getThemeTokens() {
    return this.getThemePreset()?.tokens || {};
  }

  getCustomBlocks(slideObject) {
    const canvasPresetObject = this.getCanvasPreset(slideObject);
    return Array.isArray(canvasPresetObject?.customBlocks) ? canvasPresetObject.customBlocks : [];
  }

  getInteractionConfig(slideObject) {
    return this.store.getInteractionConfigForSlide(slideObject);
  }

  getInteractionRuntime(slideObject) {
    return this.store.ensureInteractionRuntimeState(slideObject);
  }

  renderTemplateMediaSlot(currentSlideObject, interactionObject = {}, themeTokens = {}) {
    const mediaObject = currentSlideObject?.media || {};
    const mediaUrl = mediaObject?.url || currentSlideObject?.imageCenter || '';
    const mediaType = mediaObject?.type || 'image';
    const hotspotConfigObject = currentSlideObject?.settings?.hotspotConfig || {};
    const runtimeStateObject = this.getInteractionRuntime(currentSlideObject);
    const hotspotSelection = runtimeStateObject.hotspotSelection;

    if (interactionObject?.type === 'hotspot') {
      return `
        <div
          data-hotspot-surface
          style="
            position:relative;
            width:100%;
            min-height:220px;
            border-radius:20px;
            overflow:hidden;
            border:3px solid ${themeTokens.mediaBorderColor || themeTokens.accent || '#ff9f43'};
            background:${mediaUrl ? `center / cover no-repeat url('${this.escapeHtml(mediaUrl)}')` : 'linear-gradient(135deg,#7dd3fc,#1e3a8a)'};
            cursor:crosshair;
          "
        >
          <div style="position:absolute;left:${Number(hotspotConfigObject.x ?? 50)}%;top:${Number(hotspotConfigObject.y ?? 50)}%;transform:translate(-50%,-50%);width:16px;height:16px;border-radius:999px;background:${themeTokens.accent || '#2E85C7'};box-shadow:0 0 0 6px rgba(255,255,255,.55);"></div>
          ${hotspotSelection ? `<div style="position:absolute;left:${Number(hotspotSelection.x ?? 50)}%;top:${Number(hotspotSelection.y ?? 50)}%;transform:translate(-50%,-50%);width:18px;height:18px;border-radius:999px;background:#fff;box-shadow:0 0 0 4px rgba(217,72,15,.45);border:2px solid #d9480f;"></div>` : ''}
        </div>
      `;
    }

    if (!mediaUrl) {
      return `
        <div style="width:100%;min-height:180px;border-radius:20px;background:linear-gradient(135deg,#7dd3fc,#1e3a8a);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;">
          Imagine / Video / YouTube
        </div>
      `;
    }

    if (mediaType === 'audio') {
      return `<audio controls src="${this.escapeHtml(mediaUrl)}" style="width:100%;"></audio>`;
    }

    if (mediaType === 'video') {
      return `<video controls src="${this.escapeHtml(mediaUrl)}" style="width:100%;height:220px;border-radius:20px;background:#000;object-fit:cover;"></video>`;
    }

    return `<img src="${this.escapeHtml(mediaUrl)}" alt="media" style="width:100%;height:220px;display:block;border-radius:20px;object-fit:cover;">`;
  }

  renderTemplateAnswersSlot(currentSlideObject, interactionObject = {}, themeTokens = {}) {
    const runtimeStateObject = this.getInteractionRuntime(currentSlideObject);
    const answerArray = Array.isArray(currentSlideObject?.answers) ? currentSlideObject.answers : [];

    if (interactionObject.type === 'text_input') {
      const textEntryObject = currentSlideObject.settings?.textEntry || {};
      return `
        <div style="display:grid;gap:12px;">
          <input
            type="text"
            data-runtime-text-input
            value="${this.escapeHtml(runtimeStateObject.textValue || '')}"
            placeholder="${this.escapeHtml(textEntryObject.placeholder || 'Tasteaza raspunsul aici...')}"
            style="width:100%;padding:16px 18px;border-radius:16px;border:1px solid rgba(16,42,67,.12);font-size:16px;"
          >
        </div>
      `;
    }

    if (interactionObject.type === 'slider') {
      const sliderConfigObject = currentSlideObject.settings?.sliderConfig || {};
      const currentSliderValue = runtimeStateObject.sliderValue ?? sliderConfigObject.correct ?? sliderConfigObject.min ?? 0;
      return `
        <div style="display:grid;gap:12px;">
          <input
            type="range"
            min="${Number(sliderConfigObject.min ?? 0)}"
            max="${Number(sliderConfigObject.max ?? 100)}"
            step="${Number(sliderConfigObject.step ?? 1)}"
            value="${Number(currentSliderValue)}"
            data-runtime-slider="true"
            data-preview-slider="true"
            style="width:100%;accent-color:${themeTokens.accent || '#2E85C7'};"
          >
          <div class="hint">Valoare selectata: <span data-preview-slider-value>${Number(currentSliderValue)}</span></div>
        </div>
      `;
    }

    if (interactionObject.type === 'drag_drop') {
      const dragDropConfigObject = currentSlideObject.settings?.dragDropConfig || {};
      const zoneLabels = Array.isArray(dragDropConfigObject.zoneLabels) ? dragDropConfigObject.zoneLabels : [];
      const previewAssignments = dragDropConfigObject.previewAssignments || {};
      const sourceItemsMarkup = answerArray.map((answerObject, answerIndexNumber) => `
        <button
          type="button"
          draggable="true"
          data-drag-source-index="${answerIndexNumber}"
          style="padding:14px 16px;border-radius:16px;border:none;background:${this.escapeHtml(answerObject?.color || themeTokens.accent || '#2E85C7')};color:#fff;font-weight:800;cursor:grab;"
        >
          ${this.escapeHtml(answerObject?.text || `Item ${answerIndexNumber + 1}`)}
        </button>
      `).join('');
      const zonesMarkup = Array.from({ length: Number(dragDropConfigObject.zoneCount || answerArray.length || 4) }, (_, zoneIndexNumber) => {
        const assignedIndex = previewAssignments[String(zoneIndexNumber)];
        const assignedAnswerObject = Number.isInteger(Number(assignedIndex)) ? answerArray[Number(assignedIndex)] : null;
        return `
          <div style="display:grid;gap:8px;">
            <div class="hint">${this.escapeHtml(zoneLabels[zoneIndexNumber] || `Zona ${zoneIndexNumber + 1}`)}</div>
            <div
              data-drop-zone-index="${zoneIndexNumber}"
              style="min-height:88px;border-radius:18px;border:2px dashed ${themeTokens.accent || '#2E85C7'};padding:14px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.75);"
            >
              ${assignedAnswerObject ? `<div style="font-weight:800;">${this.escapeHtml(assignedAnswerObject.text || `Item ${Number(assignedIndex) + 1}`)}</div>` : '<div class="hint">Trage aici</div>'}
            </div>
            <button type="button" class="secondary" data-clear-drop-zone="${zoneIndexNumber}">Goleste</button>
          </div>
        `;
      }).join('');
      return `
        <div style="display:grid;gap:14px;">
          <div style="display:flex;flex-wrap:wrap;gap:10px;">${sourceItemsMarkup}</div>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;">${zonesMarkup}</div>
        </div>
      `;
    }

    if (interactionObject.type === 'match_pairs') {
      const matchPairsConfigObject = currentSlideObject.settings?.matchPairsConfig || {};
      const rightItems = Array.isArray(matchPairsConfigObject.rightItems) ? matchPairsConfigObject.rightItems : [];
      const previewPairs = matchPairsConfigObject.previewPairs || {};
      const activeLeftIndex = matchPairsConfigObject.activeLeftIndex;
      const leftMarkup = answerArray.map((answerObject, leftIndexNumber) => `
        <button
          type="button"
          data-match-left-index="${leftIndexNumber}"
          style="padding:14px 16px;border-radius:16px;border:${activeLeftIndex === leftIndexNumber ? `2px solid ${themeTokens.accent || '#2E85C7'}` : '1px solid rgba(16,42,67,.12)'};background:#fff;font-weight:800;text-align:left;"
        >
          ${this.escapeHtml(answerObject?.text || `Pereche ${leftIndexNumber + 1}`)}
        </button>
      `).join('');
      const rightMarkup = Array.from({ length: Number(matchPairsConfigObject.pairCount || answerArray.length || 4) }, (_, rightIndexNumber) => {
        const leftMatchIndex = Object.entries(previewPairs).find(([, value]) => Number(value) === rightIndexNumber)?.[0];
        return `
          <button
            type="button"
            data-match-right-index="${rightIndexNumber}"
            style="padding:14px 16px;border-radius:16px;border:1px solid rgba(16,42,67,.12);background:${leftMatchIndex !== undefined ? 'rgba(46,133,199,.12)' : '#fff'};font-weight:800;text-align:left;"
          >
            ${this.escapeHtml(rightItems[rightIndexNumber] || `Tinta ${rightIndexNumber + 1}`)}
          </button>
        `;
      }).join('');
      return `
        <div style="display:grid;gap:14px;">
          <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
            <div style="display:grid;gap:10px;">${leftMarkup}</div>
            <div style="display:grid;gap:10px;">${rightMarkup}</div>
          </div>
          <div class="btn-row"><button type="button" class="secondary" data-clear-match-pairs>Reseteaza perechile</button></div>
        </div>
      `;
    }

    const answerButtonsMarkup = answerArray.map((answerObject, answerIndexNumber) => {
      const isSelected = Array.isArray(runtimeStateObject.selectedAnswers) && runtimeStateObject.selectedAnswers.includes(answerIndexNumber);
      return `
        <button
          type="button"
          data-preview-answer-select="${answerIndexNumber}"
          style="
            padding:18px 20px;
            border:none;
            border-radius:18px;
            background:${this.escapeHtml(answerObject?.color || themeTokens.accent || '#2E85C7')};
            color:#fff;
            font-weight:900;
            cursor:pointer;
            box-shadow:${isSelected ? `0 0 0 3px ${themeTokens.accentStrong || '#0A5084'}, 0 14px 28px rgba(0,0,0,.16)` : '0 12px 24px rgba(0,0,0,.14)'};
          "
        >
          ${this.escapeHtml(answerObject?.text || `Raspuns ${answerIndexNumber + 1}`)}
        </button>
      `;
    }).join('');

    return `
      <div style="display:grid;grid-template-columns:repeat(${Math.min(2, Math.max(1, answerArray.length || 1))},minmax(0,1fr));gap:12px;">
        ${answerButtonsMarkup || '<div class="hint">Nu exista raspunsuri configurate.</div>'}
      </div>
    `;
  }

  renderTemplateMetaSlot(currentSlideObject) {
    const timerValue = currentSlideObject?.settings?.timer?.selected || '20s';
    const pointsValue = Number(currentSlideObject?.settings?.scoring?.basePoints || 0);
    return `
      <div class="hint" style="padding:12px 14px;border-radius:14px;background:rgba(255,255,255,.78);">
        Timer: ${this.escapeHtml(timerValue)} | Punctaj baza: ${pointsValue}
      </div>
    `;
  }

  renderTemplateSlot(slotName = '', currentSlideObject, interactionObject = {}, themeTokens = {}) {
    const normalizedSlot = String(slotName || '').trim().toLowerCase();

    if (normalizedSlot === 'title') {
      return `<div style="font-size:clamp(24px,2vw,36px);font-weight:900;color:${this.escapeHtml(themeTokens.titleColor || '#102a43')};">${this.escapeHtml(currentSlideObject?.title || 'Titlu quiz')}</div>`;
    }

    if (normalizedSlot === 'description') {
      return `<div class="hint" style="font-size:16px;line-height:1.6;">${this.escapeHtml(currentSlideObject?.description || 'Descriere / text explicativ')}</div>`;
    }

    if (normalizedSlot === 'media') {
      return this.renderTemplateMediaSlot(currentSlideObject, interactionObject, themeTokens);
    }

    if (normalizedSlot === 'answers') {
      return this.renderTemplateAnswersSlot(currentSlideObject, interactionObject, themeTokens);
    }

    if (normalizedSlot === 'evaluation') {
      return '';
    }

    if (normalizedSlot === 'meta') {
      return this.renderTemplateMetaSlot(currentSlideObject);
    }

    return '';
  }

  compileCustomTemplateHtml(templateString = '', currentSlideObject, themeTokens = {}, interactionObject = {}) {
    const runtimeStateObject = this.getInteractionRuntime(currentSlideObject);
    const answerArray = Array.isArray(currentSlideObject?.answers) ? currentSlideObject.answers : [];
    const answersText = answerArray.length
      ? answerArray.map((answerObject, answerIndexNumber) => this.escapeHtml(answerObject?.text || `Raspuns ${answerIndexNumber + 1}`)).join(' | ')
        : '';
    const customRuntimeState = currentSlideObject?.settings?.customRuntimeState || {};
    let compiledTemplate = String(templateString || '');

    compiledTemplate = compiledTemplate.replace(/\{\{\s*slot:([a-z0-9_-]+)\s*\}\}/gi, (_, slotName) => (
      this.renderTemplateSlot(slotName, currentSlideObject, interactionObject, themeTokens)
    ));
    compiledTemplate = compiledTemplate.replace(/<([a-z0-9-]+)([^>]*?)data-q-slot=["']([a-z0-9_-]+)["']([^>]*)>(.*?)<\/\1>/gis, (_, __tagName, _beforeAttrs, slotName) => (
      this.renderTemplateSlot(slotName, currentSlideObject, interactionObject, themeTokens)
    ));

    compiledTemplate = compiledTemplate
      .replaceAll('{{title}}', this.escapeHtml(currentSlideObject?.title || 'Titlu custom'))
      .replaceAll('{{description}}', this.escapeHtml(currentSlideObject?.description || 'Descriere custom'))
      .replaceAll('{{mediaUrl}}', this.escapeHtml(currentSlideObject?.media?.url || currentSlideObject?.imageCenter || ''))
      .replaceAll('{{answers}}', answersText)
      .replaceAll('{{themeAccent}}', this.escapeHtml(themeTokens.accent || '#2E85C7'))
      .replaceAll('{{themeSurface}}', this.escapeHtml(themeTokens.surface || '#ffffff'))
      .replaceAll('{{lastAction}}', this.escapeHtml(customRuntimeState.lastAction || ''))
      .replaceAll('{{runtimeMessage}}', this.escapeHtml(runtimeStateObject.message || ''))
      .replaceAll('{{scoreAwarded}}', this.escapeHtml(String(runtimeStateObject.scoreAwarded || 0)))
      .replaceAll('{{penaltyApplied}}', this.escapeHtml(String(runtimeStateObject.penaltyApplied || 0)));

    compiledTemplate = compiledTemplate.replace(/\{\{\s*answer:(\d+)\s*\}\}/gi, (_, answerNumber) => {
      const answerIndexNumber = Math.max(0, Number(answerNumber) - 1);
      return this.escapeHtml(answerArray[answerIndexNumber]?.text || `Raspuns ${answerIndexNumber + 1}`);
    });
    compiledTemplate = compiledTemplate.replace(/\{\{\s*state:input:([a-z0-9_-]+)\s*\}\}/gi, (_, inputId) => (
      this.escapeHtml(customRuntimeState.inputs?.[inputId] || '')
    ));
    compiledTemplate = compiledTemplate.replace(/\{\{\s*state:button:([a-z0-9_-]+)\s*\}\}/gi, (_, buttonId) => (
      customRuntimeState.buttons?.[buttonId] ? 'true' : 'false'
    ));

    compiledTemplate = compiledTemplate
      .replace(/data-q-input=/gi, 'data-custom-block-input=')
      .replace(/data-q-button=/gi, 'data-custom-block-button=')
      .replace(/data-q-select-answer=/gi, 'data-preview-answer-select=')
      .replace(/data-q-dropzone=/gi, 'data-drop-zone-index=')
      .replace(/data-q-clear-drop-zone=/gi, 'data-clear-drop-zone=')
      .replace(/data-q-match-left=/gi, 'data-match-left-index=')
      .replace(/data-q-match-right=/gi, 'data-match-right-index=')
      .replace(/data-q-hotspot-surface/gi, 'data-hotspot-surface')
      .replace(/data-q-clear-match-pairs(?:=(["']).*?\1)?/gi, 'data-clear-match-pairs')
      .replace(/data-q-runtime-slider(?:=(["']).*?\1)?/gi, 'data-runtime-slider="true" data-preview-slider="true"')
      .replace(/data-q-action=["']evaluate["']/gi, 'data-action="evaluate-interaction"')
      .replace(/data-q-action=["']reset["']/gi, 'data-action="reset-interaction-runtime"');

    return compiledTemplate;
  }

  renderEvaluationSummary(slideObject, interactionObject = {}) {
    const runtimeStateObject = this.getInteractionRuntime(slideObject);
    const scoringObject = typeof this.store?.getEffectiveScoringSettings === 'function'
      ? this.store.getEffectiveScoringSettings(slideObject)
      : {
          basePoints: 1000,
          speedBonus: { enabled: true, percent: 50, withinSeconds: 10 },
          wrongAnswerPenalty: { enabled: false, mode: 'equal_to_correct' },
        };
    const confirmModeLabel = interactionObject.confirmButton === false ? 'automata' : 'manuala';
    const bonusLabel = scoringObject.speedBonus?.enabled
      ? `Bonus: +${Number(scoringObject.speedBonus?.percent || 0)}% in ${Number(scoringObject.speedBonus?.withinSeconds || 0)}s`
      : 'Bonus: oprit';
    const penaltyLabel = scoringObject.wrongAnswerPenalty?.enabled
      ? `Penalizare: ${scoringObject.wrongAnswerPenalty?.mode || 'equal_to_correct'}`
      : 'Penalizare: oprita';

    if (runtimeStateObject.isCorrect === null) {
      const controlsMarkup = interactionObject.confirmButton === false ? '' : `
        <div class="btn-row" style="margin-top:14px;">
          <button type="button" class="media-link-btn" data-action="evaluate-interaction">Confirma raspunsul</button>
          <button type="button" class="secondary" data-action="reset-interaction-runtime">Reset test</button>
        </div>
      `;
      return `
        <div style="margin-top:14px;padding:14px 16px;border-radius:16px;background:rgba(255,255,255,.88);border:1px solid rgba(16,42,67,.08);">
          <div class="hint">Scor baza: ${Number(scoringObject.basePoints || 0)} | Confirmare: ${confirmModeLabel}</div>
          <div class="hint" style="margin-top:6px;">${bonusLabel} | ${penaltyLabel}</div>
          ${controlsMarkup}
        </div>
      `;
    }

    const statusColor = runtimeStateObject.isCorrect ? '#2f9e44' : '#d9480f';
    return `
      <div style="margin-top:14px;padding:14px 16px;border-radius:16px;background:rgba(255,255,255,.88);border:1px solid rgba(16,42,67,.08);">
        <div style="font-weight:900;color:${statusColor};">
          ${runtimeStateObject.isCorrect ? 'Corect' : 'Gresit'}
        </div>
        <div class="hint" style="margin-top:6px;">${this.escapeHtml(runtimeStateObject.message || '')}</div>
        <div class="hint" style="margin-top:6px;">
          Puncte: +${Number(runtimeStateObject.scoreAwarded || 0)}
          ${Number(runtimeStateObject.penaltyApplied || 0) ? ` | Penalizare: -${Number(runtimeStateObject.penaltyApplied || 0)}` : ''}
        </div>
        <div class="hint" style="margin-top:6px;">
          Scor baza: ${Number(scoringObject.basePoints || 0)} | Confirmare: ${confirmModeLabel}
        </div>
        <div class="hint" style="margin-top:6px;">${bonusLabel} | ${penaltyLabel}</div>
        <div class="btn-row" style="margin-top:12px;">
          <button type="button" class="secondary" data-action="reset-interaction-runtime">Testeaza din nou</button>
        </div>
      </div>
    `;
  }

  getCurrentAnswerColumns(slideObject, presetObject) {
    const requestedColumns = Number(presetObject?.answerColumns || 2);
    const answersCount = Array.isArray(slideObject?.answers) ? slideObject.answers.length : 0;

    if (requestedColumns >= 3 && answersCount < 3) {
      return Math.max(1, answersCount);
    }

    return Math.max(1, requestedColumns);
  }

  render() {
    const currentSlideObject = this.store.getCurrentSlide();
    const applicationDataObject = this.store.getData();

    if (!currentSlideObject) return;

    this.renderCard(currentSlideObject);
    this.renderTitle(currentSlideObject);
    this.renderMedia(currentSlideObject);
    this.renderQuestionTypeUI(currentSlideObject);
    this.renderTypeSettings(currentSlideObject);
    this.renderTypeRules(currentSlideObject);
    this.renderAudioSettings(currentSlideObject);
    this.renderAnswers(currentSlideObject);
    this.renderMeta(currentSlideObject);
    this.renderThemePreview(applicationDataObject);
    this.renderConditionalSections(currentSlideObject);
    this.renderQuizControlsFromConfig(currentSlideObject);
  }

  renderCard(currentSlideObject) {
    if (!this.dom.cardCanvas) return;

    const themeTokens = this.getThemeTokens();
    const canvasPresetObject = this.getCanvasPreset(currentSlideObject);
    const cardBackgroundUrl = currentSlideObject.background || '';
    const overlayString = themeTokens.cardOverlay || '';

    this.dom.cardCanvas.style.backgroundImage = cardBackgroundUrl
      ? `${overlayString ? `${overlayString}, ` : ''}url('${cardBackgroundUrl}')`
      : (overlayString || 'none');
    this.dom.cardCanvas.style.backgroundSize = cardBackgroundUrl ? 'cover' : '';
    this.dom.cardCanvas.style.backgroundPosition = 'center';
    this.dom.cardCanvas.style.boxShadow = themeTokens.cardShadow || '0 18px 48px rgba(0,0,0,.20)';
    this.dom.cardCanvas.style.alignItems = canvasPresetObject.titleAlign === 'left' ? 'flex-start' : 'center';

    this.applyCardChildrenStyles(currentSlideObject, canvasPresetObject, themeTokens);
  }

  applyCardChildrenStyles(currentSlideObject, canvasPresetObject, themeTokens) {
    const titleBoxElement = this.getTitleBox();
    const metaElement = this.dom.answersMetaInfo;

    this.dom.cardCanvas.style.display = canvasPresetObject.splitMode ? 'grid' : 'flex';
    this.dom.cardCanvas.style.gridTemplateColumns = canvasPresetObject.splitMode ? 'minmax(280px, 1fr) minmax(320px, 1fr)' : '';
    this.dom.cardCanvas.style.gridTemplateAreas = canvasPresetObject.splitMode
      ? `"title title" "media answers" "meta meta"`
      : '';
    this.dom.cardCanvas.style.justifyItems = canvasPresetObject.titleAlign === 'left' ? 'start' : 'center';

    if (titleBoxElement) {
      titleBoxElement.style.order = this.getStructureOrder(canvasPresetObject, 'title');
      titleBoxElement.style.gridArea = canvasPresetObject.splitMode ? 'title' : '';
      titleBoxElement.style.display = this.hasStructurePart(canvasPresetObject, 'title') ? '' : 'none';
    }

    if (this.dom.mediaCenter) {
      this.dom.mediaCenter.style.order = this.getStructureOrder(canvasPresetObject, 'media');
      this.dom.mediaCenter.style.gridArea = canvasPresetObject.splitMode ? 'media' : '';
      this.dom.mediaCenter.style.display = canvasPresetObject.mediaVisible === false ? 'none' : '';
    }

    if (this.dom.answers) {
      const answersStructureKey = this.hasStructurePart(canvasPresetObject, 'description') ? 'description' : 'answers';
      this.dom.answers.style.order = this.getStructureOrder(canvasPresetObject, answersStructureKey);
      this.dom.answers.style.gridArea = canvasPresetObject.splitMode ? 'answers' : '';
      this.dom.answers.style.display = this.hasStructurePart(canvasPresetObject, 'answers') || this.hasStructurePart(canvasPresetObject, 'description') ? '' : 'none';
    }

    if (metaElement) {
      metaElement.style.order = this.getStructureOrder(canvasPresetObject, 'meta');
      metaElement.style.gridArea = canvasPresetObject.splitMode ? 'meta' : '';
      metaElement.style.display = this.hasStructurePart(canvasPresetObject, 'meta') ? '' : 'none';
    }

    if (titleBoxElement) {
      titleBoxElement.style.width = canvasPresetObject.titleWidth || 'min(820px, 95%)';
      titleBoxElement.style.alignSelf = canvasPresetObject.titleAlign === 'left' ? 'flex-start' : 'center';
      titleBoxElement.style.background = canvasPresetObject.titleSurface || themeTokens.titleSurface || 'rgba(255,255,255,0.96)';
      titleBoxElement.style.boxShadow = themeTokens.titleShadow || '0 8px 18px rgba(0,0,0,.10)';
    }

    if (this.dom.questionTitle) {
      this.dom.questionTitle.style.textAlign = canvasPresetObject.titleAlign || 'center';
      this.dom.questionTitle.style.color = canvasPresetObject.titleColor || themeTokens.titleColor || '#0A5084';
    }

    if (this.dom.mediaCenter) {
      this.dom.mediaCenter.style.width = canvasPresetObject.splitMode ? '100%' : (canvasPresetObject.mediaWidth || 'min(720px, 90%)');
      this.dom.mediaCenter.style.aspectRatio = canvasPresetObject.mediaAspectRatio || '16/9';
      this.dom.mediaCenter.style.borderRadius = `${canvasPresetObject.mediaRadius || themeTokens.mediaRadius || 16}px`;
      this.dom.mediaCenter.style.borderWidth = `${canvasPresetObject.mediaBorderWidth || themeTokens.mediaBorderWidth || 4}px`;
      this.dom.mediaCenter.style.borderColor = canvasPresetObject.mediaBorderColor || themeTokens.mediaBorderColor || '#ff9f43';
      this.dom.mediaCenter.style.alignSelf = canvasPresetObject.titleAlign === 'left' ? 'flex-start' : 'center';
    }

    if (this.dom.answers) {
      const currentAnswerColumns = currentSlideObject.type === 'media'
        ? 1
        : this.getCurrentAnswerColumns(currentSlideObject, canvasPresetObject);

      this.dom.answers.style.width = canvasPresetObject.splitMode ? '100%' : (canvasPresetObject.answersWidth || 'min(900px, 95%)');
      this.dom.answers.style.gridTemplateColumns = `repeat(${currentAnswerColumns}, minmax(0, 1fr))`;
      this.dom.answers.style.gap = `${canvasPresetObject.answerGap || themeTokens.answerGap || 18}px`;
      this.dom.answers.style.alignSelf = canvasPresetObject.titleAlign === 'left' ? 'flex-start' : 'center';
    }
  }

  hasStructurePart(canvasPresetObject, partName) {
    return Array.isArray(canvasPresetObject?.canvasStructure) && canvasPresetObject.canvasStructure.includes(partName);
  }

  getStructureOrder(canvasPresetObject, partName) {
    const structure = Array.isArray(canvasPresetObject?.canvasStructure) ? canvasPresetObject.canvasStructure : [];
    const directIndex = structure.indexOf(partName);
    if (directIndex >= 0) return String(directIndex + 1);
    if (partName === 'media' && structure.includes('split_media_answers')) return '2';
    if ((partName === 'answers' || partName === 'description') && structure.includes('split_media_answers')) return '2';
    return '10';
  }

  renderTitle(currentSlideObject) {
    if (this.dom.questionTitle && this.dom.questionTitle !== document.activeElement) {
      this.dom.questionTitle.value = currentSlideObject.title || '';
    }

    if (this.dom.questionTitleCount) {
      const currentTitleLength = String(currentSlideObject.title || '').length;
      const maximumQuestionLength = currentSlideObject.settings?.limits?.questionTextMaxLength || 130;
      this.dom.questionTitleCount.textContent = `${currentTitleLength}/${maximumQuestionLength}`;
    }
  }

  renderMedia(currentSlideObject) {
    if (!this.dom.mediaCenter || !this.dom.mediaCenterInner) return;

    const mediaObject = currentSlideObject.media || null;

    this.dom.mediaCenter.style.backgroundImage = 'none';
    this.dom.mediaCenter.style.backgroundSize = '';
    this.dom.mediaCenter.style.backgroundPosition = '';
    this.dom.mediaCenterInner.style.display = 'block';
    this.dom.mediaCenterInner.style.width = '100%';
    this.dom.mediaCenterInner.style.height = '100%';

    if (!mediaObject || !mediaObject.url) {
      if (currentSlideObject.imageCenter) {
        this.dom.mediaCenter.style.backgroundImage = `url('${currentSlideObject.imageCenter}')`;
        this.dom.mediaCenter.style.backgroundSize = 'cover';
        this.dom.mediaCenter.style.backgroundPosition = 'center';
        this.dom.mediaCenterInner.style.display = 'none';
        return;
      }

      this.dom.mediaCenterInner.innerHTML = `
        <div class="media-icon">+</div>
        <div class="media-hint">Click pentru imagine / gif / video / YouTube / audio</div>
      `;
      return;
    }

    if (mediaObject.type === 'image' || mediaObject.type === 'gif') {
      this.dom.mediaCenter.style.backgroundImage = `url('${mediaObject.url}')`;
      this.dom.mediaCenter.style.backgroundSize = 'cover';
      this.dom.mediaCenter.style.backgroundPosition = 'center';
      this.dom.mediaCenterInner.style.display = 'none';
      return;
    }

    if (mediaObject.type === 'video') {
      this.dom.mediaCenterInner.innerHTML = `
        <video controls playsinline style="width:100%;height:100%;object-fit:cover;border-radius:inherit;display:block;background:#000;">
          <source src="${mediaObject.url}">
        </video>
      `;
      return;
    }

    if (mediaObject.type === 'youtube') {
      this.dom.mediaCenterInner.innerHTML = `
        <iframe
          src="${mediaObject.url}"
          style="width:100%;height:100%;border:none;border-radius:inherit;display:block;background:#000;"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
        ></iframe>
      `;
      return;
    }

    if (mediaObject.type === 'audio') {
      this.dom.mediaCenterInner.innerHTML = `
        <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;padding:24px;background:#111;border-radius:inherit;">
          <div style="width:min(100%,620px);background:#fff;border-radius:16px;padding:20px;box-shadow:0 10px 24px rgba(0,0,0,.18);">
            <div style="font-weight:900;color:#111;margin-bottom:12px;text-align:center;">Audio player</div>
            <audio controls style="width:100%;display:block;">
              <source src="${mediaObject.url}">
            </audio>
          </div>
        </div>
      `;
      return;
    }

    this.dom.mediaCenterInner.innerHTML = `
      <div class="media-icon">+</div>
      <div class="media-hint">Media necunoscuta</div>
    `;
  }

  renderQuestionTypeUI(currentSlideObject) {
    const questionTypeMetadataObject = this.store.getQuestionTypeMeta(currentSlideObject.originalType || currentSlideObject.type);

    if (this.dom.typeName) {
      this.dom.typeName.textContent = questionTypeMetadataObject.name;
    }

    if (this.dom.typeIcon) {
      this.dom.typeIcon.src = questionTypeMetadataObject.icon;
    }
  }

  renderTypeSettings(currentSlideObject) {
    if (!this.dom.typeSettingsDynamic) return;

    const currentAnswersCount = Array.isArray(currentSlideObject.answers) ? currentSlideObject.answers.length : 4;
    const layouts = this.store.getLayoutsByType(currentSlideObject.type || 'quiz');
    const selectedLayoutId = currentSlideObject.settings?.layoutId || layouts[0]?.id || '';
    const currentLayout = this.getCanvasPreset(currentSlideObject);
    const buildLayoutOptions = () => layouts.map((layoutItem) => `
      <option value="${this.escapeHtml(layoutItem.id)}" ${selectedLayoutId === layoutItem.id ? 'selected' : ''}>
        ${this.escapeHtml(layoutItem.localizedLabel || layoutItem.id)}
      </option>
    `).join('');

    if (currentSlideObject.type === 'media') {
      this.dom.typeSettingsDynamic.innerHTML = `
        <div class="setting-group">
          <label class="label">Layout constructor</label>
          <select class="input" data-layout-select>
            ${buildLayoutOptions()}
          </select>
        </div>

        <div class="setting-group">
          <label class="label">Layout media</label>
          <select class="input" data-media-layout>
            <option value="classic" ${currentSlideObject.settings?.mediaLayout === 'classic' ? 'selected' : ''}>Classic</option>
            <option value="big-media" ${currentSlideObject.settings?.mediaLayout === 'big-media' ? 'selected' : ''}>Big media</option>
            <option value="title-text" ${currentSlideObject.settings?.mediaLayout === 'title-text' ? 'selected' : ''}>Title + text</option>
          </select>
        </div>

        <div class="setting-group">
          <label class="label">Pozitie text</label>
          <select class="input" data-media-text-position>
            <option value="bottom" ${currentSlideObject.settings?.mediaTextPosition === 'bottom' ? 'selected' : ''}>Jos</option>
            <option value="left" ${currentSlideObject.settings?.mediaTextPosition === 'left' ? 'selected' : ''}>Stanga</option>
            <option value="right" ${currentSlideObject.settings?.mediaTextPosition === 'right' ? 'selected' : ''}>Dreapta</option>
          </select>
        </div>

        <div class="setting-group">
          <label class="label">Link YouTube / URL</label>
          <input class="input" data-media-link-url value="${this.escapeHtml(currentSlideObject.linkUrl || '')}" placeholder="https://youtube.com/...">
        </div>

        <div class="setting-group">
          <label class="label">Text buton</label>
          <input class="input" data-media-link-label value="${this.escapeHtml(currentSlideObject.linkLabel || '')}" placeholder="Vezi video / Deschide link">
        </div>
      `;
      return;
    }

    if (currentSlideObject.type === 'quiz') {
      const currentGameMode = this.store.getData()?.settings?.mode || 'single';
      const interactionObject = this.getInteractionConfig(currentSlideObject);
      const interactionType = interactionObject?.type || 'choice';
      const allowedAnswerCounts = Array.isArray(currentLayout.supportsAnswers) && currentLayout.supportsAnswers.length
        ? currentLayout.supportsAnswers
        : [2, 4, 6];
      const effectiveAllowedAnswerCounts = interactionType === 'true_false'
        ? [2]
        : (['text_input', 'slider', 'hotspot', 'drag_drop', 'match_pairs'].includes(interactionType) ? [] : allowedAnswerCounts);
      const answerCountOptions = effectiveAllowedAnswerCounts.map((answerCount) => `
        <option value="${answerCount}" ${currentAnswersCount === answerCount ? 'selected' : ''}>${answerCount} raspunsuri</option>
      `).join('');
      const textEntryObject = currentSlideObject.settings?.textEntry || {};
      const sliderConfigObject = currentSlideObject.settings?.sliderConfig || {};
      const hotspotConfigObject = currentSlideObject.settings?.hotspotConfig || {};
      const dragDropConfigObject = currentSlideObject.settings?.dragDropConfig || {};
      const matchPairsConfigObject = currentSlideObject.settings?.matchPairsConfig || {};
      const interactionSettingsMarkup = interactionType === 'text_input'
        ? `
          <div class="setting-group">
            <label class="label">Placeholder raspuns</label>
            <input class="input" data-text-placeholder value="${this.escapeHtml(textEntryObject.placeholder || 'Tasteaza raspunsul aici...')}">
          </div>
          <div class="setting-group">
            <label class="label">Raspunsuri acceptate</label>
            <input class="input" data-text-accepted value="${this.escapeHtml(Array.isArray(textEntryObject.acceptedAnswers) ? textEntryObject.acceptedAnswers.join(', ') : '')}" placeholder="Paris, paris, PARIS">
          </div>
        `
        : interactionType === 'slider'
          ? `
            <div class="setting-group">
              <label class="label">Slider min</label>
              <input class="input" type="number" data-slider-min value="${Number(sliderConfigObject.min ?? 0)}">
            </div>
            <div class="setting-group">
              <label class="label">Slider max</label>
              <input class="input" type="number" data-slider-max value="${Number(sliderConfigObject.max ?? 100)}">
            </div>
            <div class="setting-group">
              <label class="label">Valoare corecta</label>
              <input class="input" type="number" data-slider-correct value="${Number(sliderConfigObject.correct ?? 50)}">
            </div>
            <div class="setting-group">
              <label class="label">Slider step</label>
              <input class="input" type="number" data-slider-step value="${Number(sliderConfigObject.step ?? 1)}">
            </div>
          `
          : interactionType === 'hotspot'
            ? `
              <div class="setting-group">
                <label class="label">Hotspot X %</label>
                <input class="input" type="number" data-hotspot-x value="${Number(hotspotConfigObject.x ?? 50)}">
              </div>
              <div class="setting-group">
                <label class="label">Hotspot Y %</label>
                <input class="input" type="number" data-hotspot-y value="${Number(hotspotConfigObject.y ?? 50)}">
              </div>
            <div class="setting-group">
              <label class="label">Toleranta</label>
              <input class="input" type="number" data-hotspot-tolerance value="${Number(hotspotConfigObject.tolerance ?? 12)}">
            </div>
          `
          : interactionType === 'drag_drop'
            ? `
              <div class="setting-group">
                <label class="label">Numar zone drop</label>
                <input class="input" type="number" min="2" max="6" data-drag-drop-zone-count value="${Number(dragDropConfigObject.zoneCount ?? currentAnswersCount ?? 4)}">
              </div>
              <div class="setting-group">
                <label class="label">Etichete zone</label>
                <input class="input" data-drag-drop-zone-labels value="${this.escapeHtml(Array.isArray(dragDropConfigObject.zoneLabels) ? dragDropConfigObject.zoneLabels.join(', ') : '')}" placeholder="Zona 1, Zona 2, Zona 3, Zona 4">
              </div>
            `
            : interactionType === 'match_pairs'
              ? `
                <div class="setting-group">
                  <label class="label">Numar perechi</label>
                  <input class="input" type="number" min="2" max="6" data-match-pairs-count value="${Number(matchPairsConfigObject.pairCount ?? currentAnswersCount ?? 4)}">
                </div>
                <div class="setting-group">
                  <label class="label">Elemente coloana dreapta</label>
                  <input class="input" data-match-right-items value="${this.escapeHtml(Array.isArray(matchPairsConfigObject.rightItems) ? matchPairsConfigObject.rightItems.join(', ') : '')}" placeholder="Tinta 1, Tinta 2, Tinta 3, Tinta 4">
                </div>
              `
            : '';

      this.dom.typeSettingsDynamic.innerHTML = `
        <div class="setting-group">
          <label class="label">Layout constructor</label>
          <select class="input" data-layout-select>
            ${buildLayoutOptions()}
          </select>
        </div>

        <div class="hint">${this.escapeHtml(currentLayout.localizedLabel || '')}</div>

        <div class="setting-group">
          <label class="label">Mod joc</label>
          <select class="input" data-game-mode>
            <option value="single" ${currentGameMode === 'single' ? 'selected' : ''}>Joc de unul singur</option>
            <option value="group" ${currentGameMode === 'group' ? 'selected' : ''}>Joc in grup</option>
          </select>
        </div>

        ${effectiveAllowedAnswerCounts.length ? `
          <div class="setting-group">
            <label class="label">Numar raspunsuri</label>
            <select class="input" data-answer-count-select>
              ${answerCountOptions}
            </select>
          </div>
        ` : ''}

        ${interactionSettingsMarkup}
      `;
      return;
    }

    this.dom.typeSettingsDynamic.innerHTML = `
      <div class="setting-group">
          <label class="label">Layout constructor</label>
          <select class="input" data-layout-select>
            ${buildLayoutOptions()}
          </select>
        </div>

      <div class="hint">
        Acest tip de intrebare foloseste setarile standard din JSON.
      </div>
    `;
  }

  renderTypeRules(currentSlideObject) {
    if (!this.dom.typeRulesInfo) return;

    const currentTypeIdentifier = currentSlideObject.originalType || currentSlideObject.type;
    const questionTypeConfigurationObject = this.store.getQuestionTypeConfig(currentTypeIdentifier);
    const canvasPresetObject = this.getCanvasPreset(currentSlideObject);
    const interactionObject = currentSlideObject.settings?.interaction || questionTypeConfigurationObject?.settings?.interaction || {};

    if (currentSlideObject.type === 'media') {
      this.dom.typeRulesInfo.innerHTML = `
        <div class="hint">
          Pentru media poti folosi imagine, GIF, video, YouTube, audio, titlu, text si link extern.
        </div>
        <div class="hint">Layout: ${this.escapeHtml(canvasPresetObject?.localizedLabel || canvasPresetObject?.id || '-')}</div>
        <div class="hint">Interactiune: ${this.escapeHtml(interactionObject.type || 'info_only')} / ${this.escapeHtml(interactionObject.widget || 'custom_blocks')}</div>
        <div class="hint">${this.escapeHtml(canvasPresetObject?.description || '')}</div>
        <div class="hint">Blocuri custom: ${this.getCustomBlocks(currentSlideObject).length}</div>
      `;
      return;
    }

    if (questionTypeConfigurationObject) {
      const allowedMediaTypesText = Array.isArray(questionTypeConfigurationObject.settings?.allowedMediaTypes)
        ? questionTypeConfigurationObject.settings.allowedMediaTypes.join(', ')
        : 'image';
      const configuredAnswersCount = questionTypeConfigurationObject.settings?.answerCount ?? '-';
      const allowsMultipleAnswersText = questionTypeConfigurationObject.settings?.allowMultiple ? 'Da' : 'Nu';

      this.dom.typeRulesInfo.innerHTML = `
        <div class="hint"><strong>${this.escapeHtml(questionTypeConfigurationObject.desc || '')}</strong></div>
        <div class="hint">Nr. raspunsuri: ${configuredAnswersCount}</div>
        <div class="hint">Multiple answers: ${allowsMultipleAnswersText}</div>
        <div class="hint">Media permisa: ${this.escapeHtml(allowedMediaTypesText)}</div>
        <div class="hint">Layout: ${this.escapeHtml(canvasPresetObject?.localizedLabel || canvasPresetObject?.id || '-')}</div>
        <div class="hint">Interactiune: ${this.escapeHtml(interactionObject.type || 'choice')} / ${this.escapeHtml(interactionObject.widget || 'answer_buttons')}</div>
        <div class="hint">Blocuri custom: ${this.getCustomBlocks(currentSlideObject).length}</div>
      `;
      return;
    }

    this.dom.typeRulesInfo.innerHTML = `
      <div class="hint">
        Aici vor aparea limitele, media permisa, scorul si alte reguli ale tipului selectat.
      </div>
      <div class="hint">Layout: ${this.escapeHtml(canvasPresetObject?.localizedLabel || canvasPresetObject?.id || '-')}</div>
    `;
  }

  renderAudioSettings(currentSlideObject) {
    if (!this.dom.audioSettingsDynamic) return;

    if (currentSlideObject.type === 'media') {
      this.dom.audioSettingsDynamic.innerHTML = `
        <div class="hint">
          Pentru media ramane doar zona de media / muzica daca vrei sa adaugi ulterior.
        </div>
      `;
      return;
    }

    if (currentSlideObject.type !== 'quiz') {
      this.dom.audioSettingsDynamic.innerHTML = `
        <div class="hint">
          Setarile audio pentru acest tip sunt luate din JSON.
        </div>
      `;
      return;
    }

    const currentAudioConfigurationObject = currentSlideObject.settings?.audio || {};
    const currentGlobalMusicUrl = this.store.getData()?.settings?.musicUrl || '';
    const musicLibraryItems = this.store.getAudioLibraryByCategory('music');
    const correctSoundLibraryItems = this.store.getAudioLibraryByCategory('correct');
    const wrongSoundLibraryItems = this.store.getAudioLibraryByCategory('wrong');
    const gongStartLibraryItems = this.store.getAudioLibraryByCategory('gongStart');
    const gongEndLibraryItems = this.store.getAudioLibraryByCategory('gongEnd');

    const buildOptionsMarkup = (libraryItemsArray, selectedValueString) => `
      <option value="">Fara selectie</option>
      ${libraryItemsArray.map((libraryItemObject) => `
        <option value="${this.escapeHtml(libraryItemObject.url)}" ${selectedValueString === libraryItemObject.url ? 'selected' : ''}>
          ${this.escapeHtml(libraryItemObject.name)}
        </option>
      `).join('')}
    `;

    this.dom.audioSettingsDynamic.innerHTML = `
      <div class="setting-group">
        <label class="label">Muzica quiz</label>
        <select class="input" data-audio-select="music">
          ${buildOptionsMarkup(musicLibraryItems, currentGlobalMusicUrl)}
        </select>
        <input class="input" type="file" data-audio-upload="music" accept="audio/*" style="margin-top:8px;">
      </div>

      <div class="setting-group">
        <label class="label">Sunet corect</label>
        <select class="input" data-audio-select="correct">
          ${buildOptionsMarkup(correctSoundLibraryItems, currentAudioConfigurationObject.correctSoundUrl || '')}
        </select>
        <input class="input" type="file" data-audio-upload="correct" accept="audio/*" style="margin-top:8px;">
      </div>

      <div class="setting-group">
        <label class="label">Sunet gresit</label>
        <select class="input" data-audio-select="wrong">
          ${buildOptionsMarkup(wrongSoundLibraryItems, currentAudioConfigurationObject.wrongSoundUrl || '')}
        </select>
        <input class="input" type="file" data-audio-upload="wrong" accept="audio/*" style="margin-top:8px;">
      </div>

      <div class="setting-group">
        <label class="label">Gong start</label>
        <select class="input" data-audio-select="gongStart">
          ${buildOptionsMarkup(gongStartLibraryItems, currentAudioConfigurationObject.gongStartUrl || '')}
        </select>
        <input class="input" type="file" data-audio-upload="gongStart" accept="audio/*" style="margin-top:8px;">
      </div>

      <div class="setting-group">
        <label class="label">Gong end</label>
        <select class="input" data-audio-select="gongEnd">
          ${buildOptionsMarkup(gongEndLibraryItems, currentAudioConfigurationObject.gongEndUrl || '')}
        </select>
        <input class="input" type="file" data-audio-upload="gongEnd" accept="audio/*" style="margin-top:8px;">
      </div>
    `;
  }

  renderAnswers(currentSlideObject) {
    if (!this.dom.answers) return;

    const interactionObject = this.getInteractionConfig(currentSlideObject);
    const interactionType = interactionObject?.type || '';
    const canvasPresetObject = this.getCanvasPreset(currentSlideObject);

    if (canvasPresetObject?.customTemplateHtml) {
      this.renderTemplateDrivenInteraction(currentSlideObject, interactionObject);
      return;
    }

    if (currentSlideObject.type === 'media' || currentSlideObject.infoMode) {
      this.renderMediaSlideAnswers(currentSlideObject);
      return;
    }

    if (interactionType === 'text_input') {
      this.renderTextInputInteraction(currentSlideObject, interactionObject);
      return;
    }

    if (interactionType === 'slider') {
      this.renderSliderInteraction(currentSlideObject, interactionObject);
      return;
    }

    if (interactionType === 'hotspot') {
      this.renderHotspotInteraction(currentSlideObject, interactionObject);
      return;
    }

    if (interactionType === 'drag_drop') {
      this.renderDragDropInteraction(currentSlideObject, interactionObject);
      return;
    }

    if (interactionType === 'match_pairs') {
      this.renderMatchPairsInteraction(currentSlideObject, interactionObject);
      return;
    }

    if (interactionType === 'custom') {
      this.renderCustomInteraction(currentSlideObject, interactionObject);
      return;
    }

    this.renderQuizAnswersGrid(currentSlideObject);
  }

  renderTextInputInteraction(currentSlideObject, interactionObject = {}) {
    const themeTokens = this.getThemeTokens();
    const canvasPresetObject = this.getCanvasPreset(currentSlideObject);
    const textEntryObject = currentSlideObject.settings?.textEntry || {};
    const runtimeStateObject = this.getInteractionRuntime(currentSlideObject);
    const acceptedAnswersText = Array.isArray(textEntryObject.acceptedAnswers) && textEntryObject.acceptedAnswers.length
      ? `<div class="hint" style="margin-top:12px;">Raspunsuri acceptate: ${this.escapeHtml(textEntryObject.acceptedAnswers.join(', '))}</div>`
      : '';

    this.dom.answers.className = 'answers a1';
    this.dom.answers.innerHTML = `
      <div
        class="media-dynamic-wrap"
        style="
          width:${canvasPresetObject.answersWidth || 'min(900px,95%)'};
          background:${themeTokens.surface || '#fff'};
          border-radius:${canvasPresetObject.answerRadius || themeTokens.answerRadius || 22}px;
          box-shadow:${themeTokens.answerShadow || '0 14px 28px rgba(0,0,0,.16)'};
          padding:22px;
        "
      >
        <div class="hint" style="margin-bottom:12px;">Jucatorul tasteaza raspunsul si il confirma.</div>
        ${currentSlideObject.description ? `<div class="hint" style="margin-bottom:12px;">${this.escapeHtml(currentSlideObject.description)}</div>` : ''}
        <textarea
          class="answer-input"
          data-runtime-text-input
          placeholder="${this.escapeHtml(textEntryObject.placeholder || 'Tasteaza raspunsul aici...')}"
          style="width:100%;min-height:110px;background:#fff;border:1px solid rgba(16,42,67,.12);border-radius:16px;padding:16px;resize:vertical;"
        >${this.escapeHtml(runtimeStateObject.textValue || '')}</textarea>
        ${acceptedAnswersText}
      </div>
    `;
  }

  renderSliderInteraction(currentSlideObject, interactionObject = {}) {
    const themeTokens = this.getThemeTokens();
    const canvasPresetObject = this.getCanvasPreset(currentSlideObject);
    const sliderConfigObject = currentSlideObject.settings?.sliderConfig || {};
    const sliderMinimumValue = currentSlideObject.settings?.sliderMin ?? sliderConfigObject.min ?? 0;
    const sliderMaximumValue = currentSlideObject.settings?.sliderMax ?? sliderConfigObject.max ?? 100;
    const sliderCorrectValue = currentSlideObject.settings?.sliderCorrect ?? sliderConfigObject.correct ?? 50;
    const sliderStepValue = sliderConfigObject.step ?? 1;
    const runtimeStateObject = this.getInteractionRuntime(currentSlideObject);
    const currentSliderValue = Number(runtimeStateObject.sliderValue ?? sliderMinimumValue);

    this.dom.answers.className = 'answers a1';
    this.dom.answers.innerHTML = `
      <div
        class="media-dynamic-wrap"
        style="
          width:${canvasPresetObject.answersWidth || 'min(900px,95%)'};
          background:${themeTokens.surface || '#fff'};
          border-radius:${canvasPresetObject.answerRadius || themeTokens.answerRadius || 22}px;
          box-shadow:${themeTokens.answerShadow || '0 14px 28px rgba(0,0,0,.16)'};
          padding:24px;
        "
      >
        <div class="hint" style="margin-bottom:10px;">Jucatorul muta sliderul spre valoarea corecta.</div>
        <input
          type="range"
          min="${sliderMinimumValue}"
          max="${sliderMaximumValue}"
          step="${sliderStepValue}"
          value="${currentSliderValue}"
          data-runtime-slider
          data-preview-slider
          style="width:100%;"
        >
        <div class="hint" style="margin-top:12px;">
          Min: ${sliderMinimumValue}
          | Curent: <span data-preview-slider-value>${currentSliderValue}</span>
          | Corect: ${sliderCorrectValue}
          | Max: ${sliderMaximumValue}
          | Pas: ${sliderStepValue}
        </div>
      </div>
    `;
  }

  renderHotspotInteraction(currentSlideObject, interactionObject = {}) {
    const themeTokens = this.getThemeTokens();
    const canvasPresetObject = this.getCanvasPreset(currentSlideObject);
    const hotspotConfigObject = currentSlideObject.settings?.hotspotConfig || {};
    const runtimeStateObject = this.getInteractionRuntime(currentSlideObject);
    const hotspotSelection = runtimeStateObject.hotspotSelection;
    const previewBackground = currentSlideObject.media?.url || currentSlideObject.imageCenter || '';
    const backgroundStyle = previewBackground
      ? `background-image:url('${this.escapeHtml(previewBackground)}');background-size:cover;background-position:center;`
      : 'background:linear-gradient(135deg,#d8e7fb,#eef4fb);';

    this.dom.answers.className = 'answers a1';
    this.dom.answers.innerHTML = `
      <div
        class="media-dynamic-wrap"
        style="
          width:${canvasPresetObject.answersWidth || 'min(900px,95%)'};
          background:${themeTokens.surface || '#fff'};
          border-radius:${canvasPresetObject.answerRadius || themeTokens.answerRadius || 22}px;
          box-shadow:${themeTokens.answerShadow || '0 14px 28px rgba(0,0,0,.16)'};
          padding:22px;
        "
      >
        <div class="hint" style="margin-bottom:10px;">Click pe suprafata de mai jos ca sa setezi hotspotul.</div>
        <div
          data-hotspot-surface
          style="
            position:relative;
            width:100%;
            aspect-ratio:${canvasPresetObject.mediaAspectRatio || '16/9'};
            border-radius:${canvasPresetObject.mediaRadius || themeTokens.mediaRadius || 16}px;
            border:3px solid ${themeTokens.mediaBorderColor || '#ff9f43'};
            overflow:hidden;
            cursor:crosshair;
            ${backgroundStyle}
          "
        >
          <div
            style="
              position:absolute;
              left:${Number(hotspotConfigObject.x ?? 50)}%;
              top:${Number(hotspotConfigObject.y ?? 50)}%;
              width:${Math.max(16, Number(hotspotConfigObject.tolerance ?? 12) * 2)}px;
              height:${Math.max(16, Number(hotspotConfigObject.tolerance ?? 12) * 2)}px;
              transform:translate(-50%,-50%);
              border-radius:999px;
              border:4px solid #fff;
              box-shadow:0 0 0 3px ${themeTokens.accent || '#2E85C7'};
              background:rgba(46,133,199,.20);
            "
          ></div>
          ${hotspotSelection ? `
            <div
              style="
                position:absolute;
                left:${Number(hotspotSelection.x ?? 50)}%;
                top:${Number(hotspotSelection.y ?? 50)}%;
                width:18px;
                height:18px;
                transform:translate(-50%,-50%);
                border-radius:999px;
                background:${themeTokens.accentStrong || '#0A5084'};
                border:3px solid #fff;
                box-shadow:0 0 0 3px rgba(10,80,132,.25);
              "
            ></div>
          ` : ''}
        </div>
        <div class="hint" style="margin-top:12px;">X: ${Number(hotspotConfigObject.x ?? 50)}% | Y: ${Number(hotspotConfigObject.y ?? 50)}% | toleranta: ${Number(hotspotConfigObject.tolerance ?? 12)}</div>
        ${hotspotSelection ? `<div class="hint">Selectie jucator: X ${Number(hotspotSelection.x ?? 0).toFixed(1)}% | Y ${Number(hotspotSelection.y ?? 0).toFixed(1)}%</div>` : ''}
        <div class="hint">${this.escapeHtml(interactionObject.notes || 'Jucatorul trebuie sa puna pinul in zona corecta.')}</div>
      </div>
    `;
  }

  renderDragDropInteraction(currentSlideObject, interactionObject = {}) {
    const themeTokens = this.getThemeTokens();
    const canvasPresetObject = this.getCanvasPreset(currentSlideObject);
    const dragDropConfigObject = currentSlideObject.settings?.dragDropConfig || {};
    const zoneLabels = Array.isArray(dragDropConfigObject.zoneLabels) ? dragDropConfigObject.zoneLabels : [];
    const previewAssignments = dragDropConfigObject.previewAssignments || {};
    const answerArray = Array.isArray(currentSlideObject.answers) ? currentSlideObject.answers : [];

    const sourceMarkup = answerArray.map((answerObjectOrString, answerIndexNumber) => {
      const answerObject = this.normalizeAnswerObject(answerObjectOrString, answerIndexNumber);
      return `
        <button
          type="button"
          draggable="true"
          class="answer-card-clean ${answerObject.color || COLORS[answerIndexNumber % COLORS.length]}"
          data-drag-source-index="${answerIndexNumber}"
          style="min-height:${canvasPresetObject.answerMinHeight || themeTokens.answerMinHeight || 84}px;border-radius:${canvasPresetObject.answerRadius || themeTokens.answerRadius || 22}px;box-shadow:${themeTokens.answerShadow || '0 14px 28px rgba(0,0,0,.16)'};"
        >
          <span style="font-weight:800;font-size:${canvasPresetObject.answerFontSize || themeTokens.answerFontSize || 18}px;">
            ${this.escapeHtml(answerObject.text || `Element ${answerIndexNumber + 1}`)}
          </span>
        </button>
      `;
    }).join('');

    const zoneMarkup = zoneLabels.map((zoneLabel, zoneIndexNumber) => {
      const assignedAnswerIndex = previewAssignments?.[zoneIndexNumber];
      const assignedAnswerObject = Number.isInteger(assignedAnswerIndex)
        ? this.normalizeAnswerObject(answerArray[assignedAnswerIndex], assignedAnswerIndex)
        : null;

      return `
        <div
          data-drop-zone-index="${zoneIndexNumber}"
          style="
            min-height:${canvasPresetObject.answerMinHeight || themeTokens.answerMinHeight || 84}px;
            border-radius:${canvasPresetObject.answerRadius || themeTokens.answerRadius || 22}px;
            border:2px dashed ${themeTokens.accent || '#2E85C7'};
            padding:16px;
            background:${themeTokens.surface || '#fff'};
            display:flex;
            flex-direction:column;
            gap:10px;
            justify-content:center;
          "
        >
          <div class="hint" style="font-weight:800;">${this.escapeHtml(zoneLabel || `Zona ${zoneIndexNumber + 1}`)}</div>
          <div style="font-weight:700;color:#123;">${this.escapeHtml(assignedAnswerObject?.text || 'Trage aici raspunsul')}</div>
          ${assignedAnswerObject ? `<button type="button" class="link" data-clear-drop-zone="${zoneIndexNumber}">Sterge legatura</button>` : ''}
        </div>
      `;
    }).join('');

    this.dom.answers.className = 'answers a1';
    this.dom.answers.innerHTML = `
      <div
        class="media-dynamic-wrap"
        style="
          width:${canvasPresetObject.answersWidth || 'min(960px,95%)'};
          background:${themeTokens.surface || '#fff'};
          border-radius:${canvasPresetObject.answerRadius || themeTokens.answerRadius || 22}px;
          box-shadow:${themeTokens.answerShadow || '0 14px 28px rgba(0,0,0,.16)'};
          padding:22px;
        "
      >
        <div class="hint"><strong>Drag & drop:</strong> trage elementele din stanga in zonele din dreapta.</div>
        <div style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:18px;margin-top:14px;">
          <div style="display:grid;gap:12px;">
            ${sourceMarkup}
          </div>
          <div style="display:grid;gap:12px;">
            ${zoneMarkup}
          </div>
        </div>
        <div class="hint" style="margin-top:12px;">${this.escapeHtml(interactionObject.notes || 'Jucatorul trage raspunsurile in zonele corecte.')}</div>
      </div>
    `;
  }

  renderMatchPairsInteraction(currentSlideObject, interactionObject = {}) {
    const themeTokens = this.getThemeTokens();
    const canvasPresetObject = this.getCanvasPreset(currentSlideObject);
    const matchPairsConfigObject = currentSlideObject.settings?.matchPairsConfig || {};
    const rightItems = Array.isArray(matchPairsConfigObject.rightItems) ? matchPairsConfigObject.rightItems : [];
    const previewPairs = matchPairsConfigObject.previewPairs || {};
    const activeLeftIndex = Number.isInteger(matchPairsConfigObject.activeLeftIndex) ? matchPairsConfigObject.activeLeftIndex : null;
    const answerArray = Array.isArray(currentSlideObject.answers) ? currentSlideObject.answers : [];

    const leftColumnMarkup = answerArray.map((answerObjectOrString, answerIndexNumber) => {
      const answerObject = this.normalizeAnswerObject(answerObjectOrString, answerIndexNumber);
      const linkedRightIndex = previewPairs?.[answerIndexNumber];
      return `
        <button
          type="button"
          class="answer-card-clean ${answerObject.color || COLORS[answerIndexNumber % COLORS.length]} ${activeLeftIndex === answerIndexNumber ? 'answer-card-selected' : ''}"
          data-match-left-index="${answerIndexNumber}"
          style="min-height:${canvasPresetObject.answerMinHeight || themeTokens.answerMinHeight || 84}px;border-radius:${canvasPresetObject.answerRadius || themeTokens.answerRadius || 22}px;box-shadow:${themeTokens.answerShadow || '0 14px 28px rgba(0,0,0,.16)'};"
        >
          <div style="font-weight:800;font-size:${canvasPresetObject.answerFontSize || themeTokens.answerFontSize || 18}px;">
            ${this.escapeHtml(answerObject.text || `Element ${answerIndexNumber + 1}`)}
          </div>
          <div class="hint" style="margin-top:8px;">${linkedRightIndex !== undefined ? `Legat cu: ${this.escapeHtml(rightItems[linkedRightIndex] || `Tinta ${Number(linkedRightIndex) + 1}`)}` : 'Selecteaza si potriveste cu dreapta'}</div>
        </button>
      `;
    }).join('');

    const rightColumnMarkup = rightItems.map((itemText, rightIndexNumber) => {
      const pairedLeftIndex = Object.entries(previewPairs).find(([, targetValue]) => Number(targetValue) === rightIndexNumber)?.[0];
      return `
        <button
          type="button"
          class="answer-card-clean"
          data-match-right-index="${rightIndexNumber}"
          style="min-height:${canvasPresetObject.answerMinHeight || themeTokens.answerMinHeight || 84}px;border-radius:${canvasPresetObject.answerRadius || themeTokens.answerRadius || 22}px;background:${themeTokens.surfaceSecondary || '#eef4fb'};box-shadow:${themeTokens.answerShadow || '0 14px 28px rgba(0,0,0,.16)'};"
        >
          <div style="font-weight:800;">${this.escapeHtml(itemText || `Tinta ${rightIndexNumber + 1}`)}</div>
          <div class="hint" style="margin-top:8px;">${pairedLeftIndex !== undefined ? `Legat cu stanga #${Number(pairedLeftIndex) + 1}` : 'Asteapta alegere din stanga'}</div>
        </button>
      `;
    }).join('');

    this.dom.answers.className = 'answers a1';
    this.dom.answers.innerHTML = `
      <div
        class="media-dynamic-wrap"
        style="
          width:${canvasPresetObject.answersWidth || 'min(960px,95%)'};
          background:${themeTokens.surface || '#fff'};
          border-radius:${canvasPresetObject.answerRadius || themeTokens.answerRadius || 22}px;
          box-shadow:${themeTokens.answerShadow || '0 14px 28px rgba(0,0,0,.16)'};
          padding:22px;
        "
      >
        <div class="hint"><strong>Potrivire elemente:</strong> alege din stanga, apoi din dreapta ca sa creezi perechea.</div>
        <div style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:18px;margin-top:14px;">
          <div style="display:grid;gap:12px;">${leftColumnMarkup}</div>
          <div style="display:grid;gap:12px;">${rightColumnMarkup}</div>
        </div>
        <div class="btn-row" style="margin-top:14px;">
          <button type="button" class="secondary" data-clear-match-pairs>Reseteaza perechi</button>
        </div>
        <div class="hint" style="margin-top:12px;">${this.escapeHtml(interactionObject.notes || 'Jucatorul potriveste elementele din stanga cu cele din dreapta.')}</div>
      </div>
    `;
  }

  resolveCustomBlockContent(blockObject = {}, currentSlideObject) {
    const blockRole = blockObject.role || 'custom';
    const configuredContent = blockObject.content || '';

    if (blockRole === 'title') {
      return currentSlideObject.title || configuredContent || 'Titlu custom';
    }

    if (blockRole === 'description') {
      return currentSlideObject.description || configuredContent || 'Descriere custom';
    }

    if (blockRole === 'meta') {
      const selectedTimeValue = currentSlideObject.settings?.timer?.selected || '20s';
      const basePointsValue = currentSlideObject.settings?.scoring?.basePoints || 1000;
      return configuredContent || `Timer ${selectedTimeValue} | ${basePointsValue} puncte`;
    }

    if (blockRole === 'answers') {
      const answerArray = Array.isArray(currentSlideObject.answers) ? currentSlideObject.answers : [];
      return answerArray.length
        ? answerArray.map((answerObject, answerIndexNumber) => this.escapeHtml(answerObject?.text || `Raspuns ${answerIndexNumber + 1}`)).join(' | ')
        : (configuredContent || 'Zona raspunsuri');
    }

    return configuredContent || 'Bloc custom';
  }

  renderCustomBlockNode(blockObject = {}, currentSlideObject, runtimeStateObject = {}, themeTokens = {}) {
    const blockTag = blockObject.tag || 'div';
    const blockId = blockObject.id || `custom_block_${Math.random().toString(36).slice(2, 8)}`;
    const blockClasses = ['custom-runtime-block', this.escapeHtml(blockObject.className || '')].filter(Boolean).join(' ');
    const blockWidth = blockObject.width || '100%';
    const blockDisplay = blockObject.display || 'block';
    const blockBackground = blockObject.background || themeTokens.surface || '#ffffff';
    const blockColor = blockObject.color || '#102a43';
    const blockPadding = blockObject.padding || '16px';
    const blockRadius = Number(blockObject.radius || 16);
    const blockBaseStyle = `
      width:${blockWidth};
      display:${blockDisplay};
      background:${blockBackground};
      color:${blockColor};
      padding:${blockPadding};
      border-radius:${blockRadius}px;
      box-shadow:0 12px 24px rgba(0,0,0,.12);
      border:1px solid rgba(16,42,67,.08);
      box-sizing:border-box;
    `;

    if (blockTag === 'img') {
      const imageSource = blockObject.content || currentSlideObject.media?.url || currentSlideObject.imageCenter || '';
      if (!imageSource) {
        return `
          <div class="${blockClasses}" style="${blockBaseStyle};min-height:140px;display:flex;align-items:center;justify-content:center;">
            Imagine custom
          </div>
        `;
      }

      return `
        <img
          class="${blockClasses}"
          src="${this.escapeHtml(imageSource)}"
          alt="${this.escapeHtml(blockId)}"
          style="${blockBaseStyle};min-height:140px;object-fit:cover;"
        >
      `;
    }

    if (blockTag === 'input') {
      const inputValue = runtimeStateObject.inputs?.[blockId] || '';
      const inputPlaceholder = this.resolveCustomBlockContent(blockObject, currentSlideObject);
      return `
        <input
          class="${blockClasses}"
          type="text"
          data-custom-block-input="${this.escapeHtml(blockId)}"
          value="${this.escapeHtml(inputValue)}"
          placeholder="${this.escapeHtml(inputPlaceholder)}"
          style="${blockBaseStyle};outline:none;"
        >
      `;
    }

    if (blockTag === 'button') {
      const isActive = !!runtimeStateObject.buttons?.[blockId];
      return `
        <button
          type="button"
          class="${blockClasses}"
          data-custom-block-button="${this.escapeHtml(blockId)}"
          style="${blockBaseStyle};cursor:pointer;${isActive ? `box-shadow:0 0 0 3px ${themeTokens.accent || '#2E85C7'}, 0 12px 24px rgba(0,0,0,.12);` : ''}"
        >
          ${this.escapeHtml(this.resolveCustomBlockContent(blockObject, currentSlideObject))}
        </button>
      `;
    }

    const tagName = ['h1', 'h2', 'p', 'div'].includes(blockTag) ? blockTag : 'div';
    return `
      <${tagName}
        class="${blockClasses}"
        data-custom-block-id="${this.escapeHtml(blockId)}"
        style="${blockBaseStyle};margin:0;"
      >
        ${this.escapeHtml(this.resolveCustomBlockContent(blockObject, currentSlideObject))}
      </${tagName}>
    `;
  }

  renderTemplateDrivenInteraction(currentSlideObject, interactionObject = {}) {
    const themeTokens = this.getThemeTokens();
    const canvasPresetObject = this.getCanvasPreset(currentSlideObject);
    const customTemplateHtml = canvasPresetObject?.customTemplateHtml || '';

    this.dom.answers.className = 'answers a1';
    this.dom.answers.innerHTML = `
      <div
        class="media-dynamic-wrap"
        style="
          width:${canvasPresetObject.answersWidth || 'min(960px,95%)'};
          background:${themeTokens.surface || '#fff'};
          border-radius:${canvasPresetObject.answerRadius || themeTokens.answerRadius || 22}px;
          box-shadow:${themeTokens.answerShadow || '0 14px 28px rgba(0,0,0,.16)'};
          padding:22px;
        "
      >
        ${this.compileCustomTemplateHtml(customTemplateHtml, currentSlideObject, themeTokens, interactionObject)}
        <div class="hint" style="margin-top:12px;">${this.escapeHtml(interactionObject.notes || 'Template HTML custom cu sloturi si atribute data-q-* mapate pe engine-ul de joc.')}</div>
      </div>
    `;
  }

  renderCustomInteraction(currentSlideObject, interactionObject = {}) {
    const themeTokens = this.getThemeTokens();
    const canvasPresetObject = this.getCanvasPreset(currentSlideObject);
    const customBlocks = this.getCustomBlocks(currentSlideObject)
      .slice()
      .sort((firstItem, secondItem) => (firstItem.order || 0) - (secondItem.order || 0));
    const runtimeStateObject = currentSlideObject.settings?.customRuntimeState || { inputs: {}, buttons: {} };
    const customTemplateHtml = canvasPresetObject?.customTemplateHtml || '';

    if (customTemplateHtml) {
      this.dom.answers.className = 'answers a1';
      this.dom.answers.innerHTML = `
        <div
          class="media-dynamic-wrap"
          style="
            width:${canvasPresetObject.answersWidth || 'min(960px,95%)'};
            background:${themeTokens.surface || '#fff'};
            border-radius:${canvasPresetObject.answerRadius || themeTokens.answerRadius || 22}px;
            box-shadow:${themeTokens.answerShadow || '0 14px 28px rgba(0,0,0,.16)'};
            padding:22px;
          "
        >
          ${this.compileCustomTemplateHtml(customTemplateHtml, currentSlideObject, themeTokens, interactionObject)}
          <div class="hint" style="margin-top:12px;">${this.escapeHtml(interactionObject.notes || 'Template HTML custom randat din layout.')}</div>
        </div>
      `;
      return;
    }

    if (!customBlocks.length) {
      this.renderStructuredInteractionPlaceholder(currentSlideObject, interactionObject);
      return;
    }

    const blocksMarkup = customBlocks.map((blockObject) => this.renderCustomBlockNode(blockObject, currentSlideObject, runtimeStateObject, themeTokens)).join('');

    this.dom.answers.className = 'answers a1';
    this.dom.answers.innerHTML = `
      <div
        class="media-dynamic-wrap"
        style="
          width:${canvasPresetObject.answersWidth || 'min(960px,95%)'};
          background:${themeTokens.surface || '#fff'};
          border-radius:${canvasPresetObject.answerRadius || themeTokens.answerRadius || 22}px;
          box-shadow:${themeTokens.answerShadow || '0 14px 28px rgba(0,0,0,.16)'};
          padding:22px;
        "
      >
        <div class="hint"><strong>Custom interaction:</strong> blocurile de mai jos sunt randate din layoutul tau custom.</div>
        <div
          style="
            display:flex;
            flex-wrap:wrap;
            gap:${canvasPresetObject.answerGap || themeTokens.answerGap || 18}px;
            align-items:flex-start;
            margin-top:14px;
          "
        >
          ${blocksMarkup}
        </div>
        <div class="hint" style="margin-top:12px;">${this.escapeHtml(interactionObject.notes || 'Poti combina titlu, text, butoane si inputuri custom in acelasi template.')}</div>
      </div>
    `;
  }

  renderStructuredInteractionPlaceholder(currentSlideObject, interactionObject = {}) {
    const themeTokens = this.getThemeTokens();
    const canvasPresetObject = this.getCanvasPreset(currentSlideObject);
    const labelsMap = {
      drag_drop: 'drag & drop',
      match_pairs: 'potrivire elemente',
      hotspot: 'pin / hotspot',
      custom: 'custom interaction',
    };
    const labelText = labelsMap[interactionObject.type] || interactionObject.type || 'interactiune';

    this.dom.answers.className = 'answers a1';
    this.dom.answers.innerHTML = `
      <div
        class="media-dynamic-wrap"
        style="
          width:${canvasPresetObject.answersWidth || 'min(900px,95%)'};
          background:${themeTokens.surface || '#fff'};
          border-radius:${canvasPresetObject.answerRadius || themeTokens.answerRadius || 22}px;
          box-shadow:${themeTokens.answerShadow || '0 14px 28px rgba(0,0,0,.16)'};
          padding:22px;
        "
      >
        <div class="hint"><strong>Template interactiv:</strong> ${this.escapeHtml(labelText)}</div>
        <div class="hint">Widget: ${this.escapeHtml(interactionObject.widget || 'custom_blocks')}</div>
        <div class="hint">Sursa: ${this.escapeHtml(interactionObject.sourceRole || 'custom')} | Tinta: ${this.escapeHtml(interactionObject.targetRole || 'none')}</div>
        <div class="hint">${this.escapeHtml(interactionObject.notes || 'Acest tip de interactiune trebuie legat in engine-ul final de joc.')}</div>
      </div>
    `;
  }

  renderMediaSlideAnswers(currentSlideObject) {
    const canvasPresetObject = this.getCanvasPreset(currentSlideObject);
    const themeTokens = this.getThemeTokens();

    this.dom.answers.innerHTML = '';
    this.dom.answers.className = 'answers a1';

    const currentMediaLayout = currentSlideObject.settings?.mediaLayout || 'classic';
    const currentMediaTextPosition = currentSlideObject.settings?.mediaTextPosition || 'bottom';
    const currentLinkUrl = currentSlideObject.linkUrl || '';

    let textBlockClassName = 'media-text-bottom';
    if (currentMediaTextPosition === 'left') textBlockClassName = 'media-text-left';
    if (currentMediaTextPosition === 'right') textBlockClassName = 'media-text-right';

    let layoutClassName = 'media-layout-classic';
    if (currentMediaLayout === 'big-media') layoutClassName = 'media-layout-big';
    if (currentMediaLayout === 'title-text') layoutClassName = 'media-layout-title-text';

    this.dom.answers.innerHTML = `
      <div
        class="media-dynamic-wrap ${layoutClassName} ${textBlockClassName}"
        style="
          width:${canvasPresetObject.mediaTextWidth || 'min(900px,95%)'};
          background:${canvasPresetObject.titleSurface || themeTokens.surface || '#fff'};
          border-radius:${canvasPresetObject.answerRadius || themeTokens.answerRadius || 22}px;
          box-shadow:${themeTokens.answerShadow || '0 14px 28px rgba(0,0,0,.16)'};
        "
      >
        <div class="media-dynamic-text">
          <textarea
            class="answer-input"
            data-slide-description="1"
            placeholder="Scrie textul pentru slide-ul media..."
            style="width:100%;min-height:100px;color:${canvasPresetObject.titleColor || themeTokens.titleColor || '#111'};background:transparent;border:none;outline:none;resize:vertical;font-weight:700;"
          >${this.escapeHtml(currentSlideObject.description || '')}</textarea>

          ${currentLinkUrl ? `
            <div class="media-dynamic-actions">
              <a
                href="${this.escapeHtml(currentLinkUrl)}"
                target="_blank"
                rel="noopener"
                class="media-link-btn"
                style="background:${themeTokens.accent || '#2E85C7'};"
              >
                ${this.escapeHtml(currentSlideObject.linkLabel || 'Deschide link')}
              </a>
            </div>
          ` : ''}
        </div>
      </div>
    `;
  }

  renderQuizAnswersGrid(currentSlideObject) {
    this.dom.answers.innerHTML = '';
    this.dom.answers.className = 'answers answers-grid-clean';

    const canvasPresetObject = this.getCanvasPreset(currentSlideObject);
    const themeTokens = this.getThemeTokens();
    const answerArrayFromSlide = Array.isArray(currentSlideObject.answers) ? currentSlideObject.answers : [];
    const interactionObject = this.getInteractionConfig(currentSlideObject);
    const runtimeStateObject = this.getInteractionRuntime(currentSlideObject);

    answerArrayFromSlide.forEach((answerObjectOrString, answerIndexNumber) => {
      const normalizedAnswerObject = this.normalizeAnswerObject(answerObjectOrString, answerIndexNumber);
      const answerCardElement = document.createElement('div');
      const isConfiguredCorrect = normalizedAnswerObject.correct;
      const isRuntimeSelected = Array.isArray(runtimeStateObject.selectedAnswers) && runtimeStateObject.selectedAnswers.includes(answerIndexNumber);
      const selectedClass = isRuntimeSelected ? 'answer-card-selected' : '';

      answerCardElement.className = `
        answer-card-clean
        ${normalizedAnswerObject.color || COLORS[answerIndexNumber % COLORS.length]}
        ${selectedClass}
      `;

      answerCardElement.style.minHeight = `${canvasPresetObject.answerMinHeight || themeTokens.answerMinHeight || 84}px`;
      answerCardElement.style.borderRadius = `${canvasPresetObject.answerRadius || themeTokens.answerRadius || 22}px`;
      answerCardElement.style.boxShadow = isRuntimeSelected
        ? '0 0 0 4px rgba(83,234,96,.22), 0 14px 28px rgba(0,0,0,.18)'
        : (themeTokens.answerShadow || '0 14px 28px rgba(0,0,0,.16)');

      answerCardElement.addEventListener('mouseenter', () => {
        if (!answerCardElement.classList.contains('answer-card-selected')) {
          answerCardElement.style.transform = 'scale(1.02)';
        }
      });

      answerCardElement.addEventListener('mouseleave', () => {
        if (!answerCardElement.classList.contains('answer-card-selected')) {
          answerCardElement.style.transform = '';
        }
      });

      const imageWidth = canvasPresetObject.answerImageWidth || themeTokens.answerImageWidth || 140;
      const hasImage = !!normalizedAnswerObject.imageUrl;
      const answerContentMarkup = hasImage
        ? `
          <div class="answer-image-wrapper">
            <div
              class="answer-image-thumb"
              style="width:${imageWidth}px;min-width:${imageWidth}px;background-image:url('${this.escapeHtml(normalizedAnswerObject.imageUrl)}')"
            ></div>

            <button
              type="button"
              class="answer-image-delete"
              data-action="remove-answer-image"
              data-index="${answerIndexNumber}"
              title="Sterge imagine"
            >
              x
            </button>
          </div>
        `
        : `
          <input
            class="answer-text-input-clean"
            type="text"
            data-answer-input="${answerIndexNumber}"
            placeholder="Adauga raspunsul ${answerIndexNumber + 1}"
            value="${this.escapeHtml(normalizedAnswerObject.text || '')}"
            style="font-size:${canvasPresetObject.answerFontSize || themeTokens.answerFontSize || 18}px;"
          >
        `;

      answerCardElement.innerHTML = `
        <div class="answer-left-zone">
          <div class="answer-icon-box">
            ${this.getAnswerShapeSvg(answerIndexNumber)}
          </div>

          <div class="answer-content-zone">
            ${answerContentMarkup}
          </div>

          <button
            type="button"
            class="answer-image-icon-button"
            data-action="open-answer-image"
            data-index="${answerIndexNumber}"
            title="Selecteaza imagine"
          >
            <span class="answer-image-icon-button-symbol">&#128444;</span>
          </button>
          <button
            type="button"
            class="answer-image-icon-button"
            data-preview-answer-select="${answerIndexNumber}"
            title="Selecteaza pentru test"
          >
            <span class="answer-image-icon-button-symbol">${isRuntimeSelected ? '&#10003;' : '&#9654;'}</span>
          </button>
        </div>

        <div
          class="answer-correct-circle ${isConfiguredCorrect ? 'active' : ''}"
          data-answer-correct="${answerIndexNumber}"
          title="Marcheaza raspuns corect"
        ></div>
      `;

      this.dom.answers.appendChild(answerCardElement);
    });

  }

  normalizeAnswerObject(answerObjectOrString, answerIndexNumber) {
    if (typeof answerObjectOrString === 'object' && answerObjectOrString !== null) {
      return {
        text: answerObjectOrString.text || '',
        correct: !!answerObjectOrString.correct,
        color: answerObjectOrString.color || COLORS[answerIndexNumber % COLORS.length],
        imageUrl: answerObjectOrString.imageUrl || '',
      };
    }

    return {
      text: answerObjectOrString || '',
      correct: false,
      color: COLORS[answerIndexNumber % COLORS.length],
      imageUrl: '',
    };
  }

  getAnswerShapeSvg(answerIndexNumber) {
    const svgList = [
      `<svg viewBox="0 0 32 32" width="30" height="30" aria-hidden="true"><path d="M16 5 L28 27 L4 27 Z" fill="white"></path></svg>`,
      `<svg viewBox="0 0 32 32" width="30" height="30" aria-hidden="true"><path d="M16 4 L28 16 L16 28 L4 16 Z" fill="white"></path></svg>`,
      `<svg viewBox="0 0 32 32" width="30" height="30" aria-hidden="true"><circle cx="16" cy="16" r="12" fill="white"></circle></svg>`,
      `<svg viewBox="0 0 32 32" width="30" height="30" aria-hidden="true"><rect x="6" y="6" width="20" height="20" fill="white"></rect></svg>`,
      `<svg viewBox="0 0 32 32" width="30" height="30" aria-hidden="true"><path d="M16 4 L20 12 L29 13 L22 19 L24 28 L16 23 L8 28 L10 19 L3 13 L12 12 Z" fill="white"></path></svg>`,
      `<svg viewBox="0 0 32 32" width="30" height="30" aria-hidden="true"><path d="M16 4 C21 8, 27 10, 27 16 C27 23, 21 27, 16 28 C11 27, 5 23, 5 16 C5 10, 11 8, 16 4 Z" fill="white"></path></svg>`,
    ];

    return svgList[answerIndexNumber % svgList.length];
  }

  renderMeta(currentSlideObject) {
    if (!this.dom.answersMetaInfo) return;

    const canvasPresetObject = this.getCanvasPreset(currentSlideObject);
    const interactionObject = this.getInteractionConfig(currentSlideObject);

    if (currentSlideObject.type === 'media') {
      const customBlocks = this.getCustomBlocks(currentSlideObject);
      this.dom.answersMetaInfo.innerHTML = `
        <div class="hint">
          Media slide: imagine / gif / video / audio + descriere + link extern.
        </div>
        <div class="hint">Layout activ: ${this.escapeHtml(canvasPresetObject?.localizedLabel || canvasPresetObject?.id || '-')}</div>
        <div class="hint">Blocuri custom: ${customBlocks.length ? customBlocks.map((blockObject) => `${blockObject.tag}:${blockObject.role || 'custom'}`).join(', ') : '-'}</div>
        ${this.renderEvaluationSummary(currentSlideObject, interactionObject)}
      `;
      return;
    }

    const answersCountNumber = Array.isArray(currentSlideObject.answers) ? currentSlideObject.answers.length : 0;
    const answerMaximumLength = currentSlideObject.settings?.limits?.answerTextMaxLength || 80;
    const selectedCorrectAnswersCount = this.getSelectedCorrectAnswersCount(currentSlideObject.answers || []);
    const customBlocks = this.getCustomBlocks(currentSlideObject);

    this.dom.answersMetaInfo.innerHTML = `
      <div class="hint">
        Raspunsuri: ${answersCountNumber} | max ${answerMaximumLength} caractere / raspuns | corecte selectate: ${selectedCorrectAnswersCount}
      </div>
      <div class="hint">Layout activ: ${this.escapeHtml(canvasPresetObject?.localizedLabel || canvasPresetObject?.id || '-')}</div>
      <div class="hint">Blocuri custom: ${customBlocks.length ? customBlocks.map((blockObject) => `${blockObject.tag}:${blockObject.role || 'custom'}`).join(', ') : '-'}</div>
      ${this.renderEvaluationSummary(currentSlideObject, interactionObject)}
    `;
  }

  getSelectedCorrectAnswersCount(answerArray = []) {
    return answerArray.filter((answerObject) => typeof answerObject === 'object' && answerObject?.correct).length;
  }

  renderThemePreview(applicationDataObject) {
    const activeThemeObject = this.getThemePreset();

    if (this.dom.themePreview) {
      this.dom.themePreview.style.backgroundImage = applicationDataObject.settings?.themeUrl
        ? `${activeThemeObject?.tokens?.cardOverlay ? `${activeThemeObject.tokens.cardOverlay}, ` : ''}url('${applicationDataObject.settings.themeUrl}')`
        : 'none';
      this.dom.themePreview.style.backgroundSize = 'cover';
      this.dom.themePreview.style.backgroundPosition = 'center';
      this.dom.themePreview.style.border = `2px solid ${activeThemeObject?.tokens?.accent || '#2E85C7'}`;
    }

    if (this.dom.themeName) {
      this.dom.themeName.textContent = applicationDataObject.settings?.theme || 'standart';
      this.dom.themeName.style.color = activeThemeObject?.tokens?.accentStrong || '#0A5084';
    }
  }

  renderConditionalSections(currentSlideObject) {
    const quizSettingRowElement = this.getQuizSettingRow();
    const timeTitleElement = this.getTimeTitle();
    const bonusTitleElement = this.getBonusTitle();

    if (currentSlideObject.type === 'media') {
      if (quizSettingRowElement) quizSettingRowElement.style.display = 'none';
      if (this.dom.timeGrid) this.dom.timeGrid.style.display = 'none';
      if (timeTitleElement) timeTitleElement.style.display = 'none';

      if (this.dom.bonusToggle) {
        const bonusRowElement = this.dom.bonusToggle.closest('.bonus-row');
        if (bonusRowElement) bonusRowElement.style.display = 'none';
        if (bonusTitleElement) bonusTitleElement.style.display = 'none';
      }

      if (this.dom.bonusRange) this.dom.bonusRange.style.display = 'none';
      const bonusHintElement = document.getElementById('bonus-hint');
      if (bonusHintElement) bonusHintElement.style.display = 'none';
      return;
    }

    if (quizSettingRowElement) quizSettingRowElement.style.display = '';
    if (this.dom.timeGrid) this.dom.timeGrid.style.display = '';
    if (timeTitleElement) timeTitleElement.style.display = '';

    if (this.dom.bonusToggle) {
      const bonusRowElement = this.dom.bonusToggle.closest('.bonus-row');
      if (bonusRowElement) bonusRowElement.style.display = '';
      if (bonusTitleElement) bonusTitleElement.style.display = '';
    }

    if (this.dom.bonusRange) this.dom.bonusRange.style.display = '';
    const bonusHintElement = document.getElementById('bonus-hint');
    if (bonusHintElement) bonusHintElement.style.display = '';
  }

  renderQuizControlsFromConfig(currentSlideObject) {
    if (currentSlideObject.type !== 'quiz') return;

    const questionTypeConfigurationObject = this.store.getQuestionTypeConfig(currentSlideObject.originalType || 'quiz');
    const commonConfigurationObject = this.store.getCommonConfig();
    const defaultTimerObject = commonConfigurationObject.defaults?.timer || {};
    const defaultScoringObject = commonConfigurationObject.defaults?.scoring || {};

    const allowMultipleAnswers = !!questionTypeConfigurationObject?.settings?.allowMultiple;
    const selectedTimeValue = currentSlideObject.settings?.timer?.selected || questionTypeConfigurationObject?.settings?.timer?.selected || defaultTimerObject.selected || '20s';
    const speedBonusEnabledValue = !!(currentSlideObject.settings?.scoring?.speedBonus?.enabled ?? questionTypeConfigurationObject?.settings?.scoring?.speedBonus?.enabled ?? defaultScoringObject?.speedBonus?.enabled);
    const speedBonusSecondsValue = Number(currentSlideObject.settings?.scoring?.speedBonus?.withinSeconds || questionTypeConfigurationObject?.settings?.scoring?.speedBonus?.withinSeconds || defaultScoringObject?.speedBonus?.withinSeconds || 10);

    const quizSettingRowElement = this.getQuizSettingRow();
    if (quizSettingRowElement) {
      quizSettingRowElement.style.display = allowMultipleAnswers ? '' : 'none';
    }

    if (this.dom.selectMultiple) {
      this.dom.selectMultiple.checked = currentSlideObject.selectType === 'multiple';
      this.dom.selectMultiple.disabled = !allowMultipleAnswers;
    }

    if (this.dom.timeGrid) {
      const timeRadioNodeList = this.dom.timeGrid.querySelectorAll('input[type="radio"][name="time"]');
      timeRadioNodeList.forEach((radioElement) => {
        radioElement.checked = radioElement.value === selectedTimeValue;
      });
    }

    if (this.dom.bonusToggle) {
      this.dom.bonusToggle.checked = speedBonusEnabledValue;
    }

    if (this.dom.bonusStatus) {
      this.dom.bonusStatus.textContent = speedBonusEnabledValue ? 'ON' : 'OFF';
    }

    if (this.dom.bonusRange) {
      this.dom.bonusRange.value = String(speedBonusSecondsValue);
    }

    if (this.dom.bonusTimeLabel) {
      this.dom.bonusTimeLabel.textContent = `${speedBonusSecondsValue}s`;
    }

    if (this.dom.musicName) {
      this.dom.musicName.textContent = this.store.getData()?.settings?.musicUrl ? 'Muzica selectata' : 'Fara muzica';
    }
  }
}
