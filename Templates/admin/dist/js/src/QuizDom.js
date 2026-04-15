export class QuizDom {
  constructor(root = document) {
    this.root = root;

    this.qs = (selector, scope = this.root) => {
      try {
        return scope.querySelector(selector) || null;
      } catch {
        return null;
      }
    };

    this.slidesList = this.qs('#slides-list');

    this.cardCanvas = this.qs('#card-canvas');
    this.questionTitle = this.qs('#question-title');
    this.questionTitleCount = this.qs('#question-title-count');
    this.mediaCenter = this.qs('#media-center');
    this.mediaCenterInner = this.qs('#media-center-inner');
    this.answers = this.qs('#answers');
    this.answersMetaInfo = this.qs('#answers-meta-info');

    this.themePreview = this.qs('#theme-preview');
    this.themeName = this.qs('#theme-name');

    this.typeIcon = this.qs('#type-icon');
    this.typeName = this.qs('#type-name');
    this.typeSettingsDynamic = this.qs('#type-settings-dynamic');
    this.typeRulesInfo = this.qs('#type-rules-info');
    this.audioSettingsDynamic = this.qs('#audio-settings-dynamic');

    this.selectMultiple = this.qs('#select-multiple');
    this.timeGrid = this.qs('#time-grid');
    this.bonusToggle = this.qs('#bonus-toggle');
    this.bonusRange = this.qs('#bonus-range');
    this.bonusStatus = this.qs('#bonus-status');
    this.bonusTimeLabel = this.qs('#bonus-time-label');

    this.musicIcon = this.qs('#music-icon');
    this.musicName = this.qs('#music-name');

    this.popup = this.qs('#popup');
    this.popupTitle = this.qs('#popup-title');
    this.popupBody = this.qs('#popup-body');
    this.popupFoot = this.qs('#popup-foot');
  }
}