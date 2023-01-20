/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Quiz plugin.
 */
registry.add('UJMExoBundle', {
  resources: {
    'ujm_exercise': () => { return import(/* webpackChunkName: "plugin-exo-quiz-resource" */ '#/plugin/exo/resources/quiz') }
  },
  data: {
    types: {
      'medias'     : () => { return import(/* webpackChunkName: "exo-data-medias" */      '#/plugin/exo/data/types/medias') },
      'score_rules': () => { return import(/* webpackChunkName: "exo-data-score_rules" */ '#/plugin/exo/data/types/score-rules') }
    }
  },

  quizItems: {
    // questions
    'choice'   : () => { return import(/* webpackChunkName: "quiz-item-choice" */    '#/plugin/exo/items/choice') },
    'cloze'    : () => { return import(/* webpackChunkName: "quiz-item-cloze" */     '#/plugin/exo/items/cloze') },
    'graphic'  : () => { return import(/* webpackChunkName: "quiz-item-graphic" */   '#/plugin/exo/items/graphic') },
    'grid'     : () => { return import(/* webpackChunkName: "quiz-item-grid" */      '#/plugin/exo/items/grid') },
    'match'    : () => { return import(/* webpackChunkName: "quiz-item-match" */     '#/plugin/exo/items/match') },
    'open'     : () => { return import(/* webpackChunkName: "quiz-item-open" */      '#/plugin/exo/items/open') },
    'ordering' : () => { return import(/* webpackChunkName: "quiz-item-ordering" */  '#/plugin/exo/items/ordering') },
    'pair'     : () => { return import(/* webpackChunkName: "quiz-item-pair" */      '#/plugin/exo/items/pair') },
    'selection': () => { return import(/* webpackChunkName: "quiz-item-selection" */ '#/plugin/exo/items/selection') },
    'set'      : () => { return import(/* webpackChunkName: "quiz-item-set" */       '#/plugin/exo/items/set') },
    'words'    : () => { return import(/* webpackChunkName: "quiz-item-boolean" */   '#/plugin/exo/items/words') },

    // contents
    'audio'    : () => { return import(/* webpackChunkName: "quiz-item-audio" */     '#/plugin/exo/contents/audio') },
    'image'    : () => { return import(/* webpackChunkName: "quiz-item-image" */     '#/plugin/exo/contents/image') },
    'text'     : () => { return import(/* webpackChunkName: "quiz-item-text" */      '#/plugin/exo/contents/text') },
    'video'    : () => { return import(/* webpackChunkName: "quiz-item-video" */     '#/plugin/exo/contents/video') },
    'resource' : () => { return import(/* webpackChunkName: "quiz-item-resource" */  '#/plugin/exo/contents/resource') }
  }
})
