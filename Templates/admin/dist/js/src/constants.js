export const CONFIG_PATHS = {
  BUILDER_CORE_JSON: new URL('../configs/builder-core.json', import.meta.url).href,
  BUILDER_LAYOUTS_JSON: new URL('../configs/builder-layouts.json', import.meta.url).href,
  BUILDER_THEMES_JSON: new URL('../configs/builder-themes.json', import.meta.url).href,
  BUILDER_AUDIO_JSON: new URL('../configs/builder-audio.json', import.meta.url).href,
  QUESTION_TYPES_JSON: new URL('../configs/question-types.json', import.meta.url).href,
  QUIZZES_JSON: new URL('../configs/quizzes.json', import.meta.url).href,
  CONFIG_BUILDER_API: new URL('../configs/config-builder-api.php', import.meta.url).href,
  QUIZ_LIBRARY_JSON: new URL('../configs/quiz-library.json', import.meta.url).href,
};

export const STOCK_IMAGES = [
  {
    id: 'img_1',
    name: 'Education 1',
    url: 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?q=80&w=1200'
  },
  {
    id: 'img_2',
    name: 'Technology 1',
    url: 'https://images.unsplash.com/photo-1518770660439-4636190af475?q=80&w=1200'
  },
  {
    id: 'img_3',
    name: 'Abstract 1',
    url: 'https://images.unsplash.com/photo-1557683316-973673baf926?q=80&w=1200'
  },
  {
    id: 'img_4',
    name: 'School 1',
    url: 'https://images.unsplash.com/photo-1434031215662-72ee337ec3b3?q=80&w=1200'
  }
];

export const THEMES = [
  {
    id: 'theme_1',
    name: 'Abstract Blue',
    url: 'https://images.unsplash.com/photo-1557683316-973673baf926?q=80&w=1200'
  },
  {
    id: 'theme_2',
    name: 'Education',
    url: 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?q=80&w=1200'
  },
  {
    id: 'theme_3',
    name: 'Technology',
    url: 'https://images.unsplash.com/photo-1518770660439-4636190af475?q=80&w=1200'
  },
  {
    id: 'theme_4',
    name: 'School',
    url: 'https://images.unsplash.com/photo-1434031215662-72ee337ec3b3?q=80&w=1200'
  }
];

export const COLORS = ['purple', 'orange', 'green', 'yellow', 'blue', 'red'];

export const STOCK_AUDIO = [
  { id: 'lofi_1', name: 'LoFi 1', url: 'https://site-ul-tau.com/audio/lofi1.mp3' },
  { id: 'calm_2', name: 'Calm 2', url: 'https://site-ul-tau.com/audio/calm2.mp3' },
];

export const SUPPORTED_TIMERS = ['5s', '10s', '20s', '30s', '60s', '90s', '120s', '240s', '300s'];
export const SCORE_OPTIONS = [100, 200, 300, 400, 500, 1000, 2000, 0];

export const DEFAULT_LIMITS = {
  questionTextMaxLength: 130,
  answerTextMaxLength: 80,
  puzzleMinBlocks: 2,
  puzzleMaxBlocks: 5,
};

export const DEFAULT_TYPE_LABELS = {
  confirm: 'Confirmă răspunsul',
};

export const DEFAULT_AUDIO_SETTINGS = {
  masterEnabled: true,
  musicEnabled: false,
  musicTrackId: '',
  musicUrl: '',
  gongEnabled: false,
  gongStartUrl: '',
  gongEndUrl: '',
  answerSoundsEnabled: true,
  correctSoundId: '',
  correctSoundUrl: '',
  wrongSoundId: '',
  wrongSoundUrl: '',
};

export const DEFAULT_TIMER_SETTINGS = {
  enabled: true,
  selected: '20s',
  allowed: SUPPORTED_TIMERS,
};

export const DEFAULT_SCORING_SETTINGS = {
  basePoints: 1000,
  allowedBasePoints: SCORE_OPTIONS,
  doubleStandard: false,
  noPoints: false,
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
export const DEFAULT_SLIDE = {
  id: null,
  type: 'quiz',
  title: '',
  description: '',
  background: '',
  imageCenter: '',
  media: null,

  answers: ['', '', '', ''],
  answerImages: ['', '', '', ''],
  correctAnswerIndex: null,
  correctAnswerIndexes: [],
  selectType: 'single',

  infoMode: false,
  linkUrl: '',
  linkLabel: '',
  mode: 'single',

  musicUrl: '',

  settings: {
    timer: {
      selected: '20s',
    },
    scoring: {
      basePoints: 1000,
    },
    audio: {
      musicEnabled: false,
      answerSoundsEnabled: false,
      gongEnabled: false,
      musicUrl: '',
      correctSoundUrl: '',
      wrongSoundUrl: '',
      gongStartUrl: '',
      gongEndUrl: '',
    },
    imageReveal: {
      mode: 'original',
    },
    limits: {
      questionTextMaxLength: 130,
      answerTextMaxLength: 80,
    }
  }
};
