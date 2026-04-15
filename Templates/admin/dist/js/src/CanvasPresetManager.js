import { THEMES } from './constants.js';

const DEFAULT_THEME_TOKENS = {
  accent: '#2E85C7',
  accentStrong: '#0A5084',
  surface: 'rgba(255,255,255,0.94)',
  titleSurface: 'rgba(255,255,255,0.96)',
  titleColor: '#0A5084',
  mediaBorderColor: '#ff9f43',
  mediaBorderWidth: 4,
  mediaRadius: 16,
  answerRadius: 22,
  answerGap: 18,
  answerMinHeight: 84,
  answerFontSize: 18,
  answerColumns: 2,
  answerImageWidth: 140,
  cardShadow: '0 18px 48px rgba(0,0,0,.20)',
  titleShadow: '0 8px 18px rgba(0,0,0,.10)',
  answerShadow: '0 14px 28px rgba(0,0,0,.16)',
};

const DEFAULT_TITLE_VARIANTS = {
  default: {
    titleAlign: 'center',
    titleWidth: 'min(820px, 95%)',
  },
  editorial: {
    titleAlign: 'left',
    titleWidth: 'min(920px, 96%)',
  },
  compact: {
    titleAlign: 'center',
    titleWidth: 'min(760px, 92%)',
  },
};

const DEFAULT_MEDIA_VARIANTS = {
  top_large: {
    mediaVisible: true,
    mediaWidth: 'min(720px, 90%)',
    mediaAspectRatio: '16/9',
  },
  top_medium: {
    mediaVisible: true,
    mediaWidth: 'min(620px, 82%)',
    mediaAspectRatio: '16/9',
  },
  cinematic: {
    mediaVisible: true,
    mediaWidth: 'min(860px, 94%)',
    mediaAspectRatio: '21/9',
  },
  left_panel: {
    mediaVisible: true,
    mediaWidth: '100%',
    mediaAspectRatio: '4/3',
    splitMode: true,
  },
  hidden: {
    mediaVisible: false,
  },
};

const DEFAULT_ANSWERS_VARIANTS = {
  grid_2_columns: {
    answerColumns: 2,
    answerGap: 18,
    answerMinHeight: 84,
    answerRadius: 22,
    answerFontSize: 18,
    answerImageWidth: 140,
    answersWidth: 'min(900px, 95%)',
  },
  vertical_stack: {
    answerColumns: 1,
    answerGap: 14,
    answerMinHeight: 74,
    answerRadius: 18,
    answerFontSize: 17,
    answerImageWidth: 110,
    answersWidth: 'min(820px, 92%)',
  },
  grid_1_column_large: {
    answerColumns: 1,
    answerGap: 16,
    answerMinHeight: 88,
    answerRadius: 16,
    answerFontSize: 18,
    answerImageWidth: 150,
    answersWidth: 'min(960px, 95%)',
  },
  media_text_card: {
    answerColumns: 1,
    answersWidth: 'min(900px,95%)',
    mediaTextWidth: 'min(900px,95%)',
  },
};

const DEFAULT_LAYOUTS = [
  {
    id: 'quiz_classic_image_top',
    label: { en: 'Quiz classic image top', ro: 'Quiz clasic cu imagine sus' },
    slideType: 'quiz',
    canvasStructure: ['title', 'media', 'answers', 'meta'],
    answersVariant: 'grid_2_columns',
    mediaVariant: 'top_large',
    titleVariant: 'default',
    supportsAnswers: [2, 4, 6],
  },
  {
    id: 'quiz_classic_no_media',
    label: { en: 'Quiz no media', ro: 'Quiz fara media' },
    slideType: 'quiz',
    canvasStructure: ['title', 'answers', 'meta'],
    answersVariant: 'grid_2_columns',
    mediaVariant: 'hidden',
    titleVariant: 'default',
    supportsAnswers: [2, 4, 6],
  },
  {
    id: 'quiz_answers_vertical',
    label: { en: 'Quiz answers vertical', ro: 'Quiz raspunsuri verticale' },
    slideType: 'quiz',
    canvasStructure: ['title', 'media', 'answers', 'meta'],
    answersVariant: 'vertical_stack',
    mediaVariant: 'top_medium',
    titleVariant: 'default',
    supportsAnswers: [2, 4, 6],
  },
  {
    id: 'quiz_media_left_answers_right',
    label: { en: 'Quiz media left answers right', ro: 'Quiz media stanga raspunsuri dreapta' },
    slideType: 'quiz',
    canvasStructure: ['title', 'split_media_answers', 'meta'],
    answersVariant: 'vertical_stack',
    mediaVariant: 'left_panel',
    titleVariant: 'editorial',
    supportsAnswers: [2, 4, 6],
  },
  {
    id: 'quiz_only_answers_big',
    label: { en: 'Quiz only answers big', ro: 'Quiz doar raspunsuri mari' },
    slideType: 'quiz',
    canvasStructure: ['title', 'answers', 'meta'],
    answersVariant: 'grid_1_column_large',
    mediaVariant: 'hidden',
    titleVariant: 'compact',
    supportsAnswers: [2, 4],
  },
  {
    id: 'media_classic',
    label: { en: 'Media classic', ro: 'Media clasic' },
    slideType: 'media',
    canvasStructure: ['title', 'media', 'description'],
    answersVariant: 'media_text_card',
    mediaVariant: 'top_large',
    titleVariant: 'default',
  },
  {
    id: 'media_title_text',
    label: { en: 'Media title text', ro: 'Media titlu text' },
    slideType: 'media',
    canvasStructure: ['title', 'description', 'media'],
    answersVariant: 'media_text_card',
    mediaVariant: 'top_medium',
    titleVariant: 'editorial',
  },
];

const DEFAULT_LAYOUT_BY_SLIDE_TYPE = {
  quiz: 'quiz_classic_image_top',
  media: 'media_classic',
};

export class CanvasPresetManager {
  constructor(config = null) {
    this.setConfig(config);
  }

  setConfig(config) {
    this.config = config || null;
  }

  getLocalizedText(value, lang = 'en') {
    if (typeof value === 'string') return value;
    if (!value || typeof value !== 'object') return '';
    return value[lang] || value.en || Object.values(value)[0] || '';
  }

  getThemeCatalog() {
    const configuredThemes = Array.isArray(this.config?.assets?.themes) && this.config.assets.themes.length
      ? this.config.assets.themes
      : THEMES;

    return configuredThemes.map((themeItem, themeIndex) => {
      const fallbackTheme = THEMES[themeIndex % THEMES.length] || THEMES[0] || {};

      return {
        id: themeItem.id || fallbackTheme.id || `theme_${themeIndex + 1}`,
        name: themeItem.name || fallbackTheme.name || `Theme ${themeIndex + 1}`,
        url: themeItem.url || fallbackTheme.url || '',
        description: themeItem.description || '',
        tokens: {
          ...DEFAULT_THEME_TOKENS,
          ...(fallbackTheme.tokens || {}),
          ...(themeItem.tokens || {}),
        },
      };
    });
  }

  getThemeById(themeId) {
    if (!themeId) return null;
    return this.getThemeCatalog().find((themeItem) => themeItem.id === themeId) || null;
  }

  getBuilderConfig() {
    return this.config?.common?.builder || {};
  }

  getTitleVariants() {
    return {
      ...DEFAULT_TITLE_VARIANTS,
      ...(this.getBuilderConfig().titleVariants || {}),
    };
  }

  getMediaVariants() {
    return {
      ...DEFAULT_MEDIA_VARIANTS,
      ...(this.getBuilderConfig().mediaVariants || {}),
    };
  }

  getAnswersVariants() {
    return {
      ...DEFAULT_ANSWERS_VARIANTS,
      ...(this.getBuilderConfig().answersVariants || {}),
    };
  }

  getLayoutRegistry() {
    const registry = this.getBuilderConfig().layoutRegistry;
    return Array.isArray(registry) && registry.length ? registry : DEFAULT_LAYOUTS;
  }

  getDefaultLayoutId(type = 'quiz') {
    const configuredDefaults = this.getBuilderConfig().defaultLayoutBySlideType || {};
    return configuredDefaults[type] || DEFAULT_LAYOUT_BY_SLIDE_TYPE[type] || this.getLayoutsBySlideType(type)[0]?.id || null;
  }

  getLayoutsBySlideType(type = 'quiz', lang = 'en') {
    return this.getLayoutRegistry()
      .filter((layoutItem) => layoutItem.slideType === type)
      .map((layoutItem) => ({
        ...layoutItem,
        localizedLabel: this.getLocalizedText(layoutItem.label, lang),
      }));
  }

  getLayoutById(type = 'quiz', layoutId = null, lang = 'en') {
    const layouts = this.getLayoutsBySlideType(type, lang);
    if (!layoutId) {
      return layouts.find((layoutItem) => layoutItem.id === this.getDefaultLayoutId(type)) || layouts[0] || null;
    }

    return layouts.find((layoutItem) => layoutItem.id === layoutId) || layouts.find((layoutItem) => layoutItem.id === this.getDefaultLayoutId(type)) || layouts[0] || null;
  }

  resolveLayout(type = 'quiz', layoutId = null, lang = 'en') {
    const layout = this.getLayoutById(type, layoutId, lang);
    if (!layout) return null;

    const titleVariant = this.getTitleVariants()[layout.titleVariant || 'default'] || {};
    const mediaVariant = this.getMediaVariants()[layout.mediaVariant || 'top_large'] || {};
    const answersVariant = this.getAnswersVariants()[layout.answersVariant || 'grid_2_columns'] || {};

    return {
      ...layout,
      ...titleVariant,
      ...mediaVariant,
      ...answersVariant,
      customBlocks: Array.isArray(layout.customBlocks) ? layout.customBlocks : [],
      localizedLabel: this.getLocalizedText(layout.label, lang),
    };
  }

  normalizeSlideSettings(slideObject, lang = 'en') {
    if (!slideObject) return slideObject;

    slideObject.settings = slideObject.settings || {};

    if (!slideObject.settings.layoutId) {
      slideObject.settings.layoutId = this.getDefaultLayoutId(slideObject.type || 'quiz');
    }

    const resolvedLayout = this.resolveLayout(slideObject.type || 'quiz', slideObject.settings.layoutId, lang);
    if (resolvedLayout) {
      slideObject.settings.layoutId = resolvedLayout.id;
    }

    return slideObject;
  }
}
