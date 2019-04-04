import {trans} from '#/main/app/intl/translation'

// Shuffle
const SHUFFLE_NEVER  = 'never'
const SHUFFLE_ALWAYS = 'always'
const SHUFFLE_ONCE   = 'once'

const SHUFFLE_MODES = {
  [SHUFFLE_NEVER] : trans('never', {}, 'quiz'),
  [SHUFFLE_ALWAYS]: trans('at_each_attempt', {}, 'quiz'),
  [SHUFFLE_ONCE]  : trans('at_first_attempt', {}, 'quiz')
}


// Quiz picking
const QUIZ_PICKING_DEFAULT = 'standard'
const QUIZ_PICKING_TAGS    = 'tags'

const QUIZ_PICKINGS = {
  [QUIZ_PICKING_DEFAULT]: trans('quiz_picking_steps', {}, 'quiz'),
  [QUIZ_PICKING_TAGS]   : trans('quiz_picking_tags', {}, 'quiz')
}


// Quiz results
const QUIZ_RESULTS_AT_VALIDATION   = 'validation'
const QUIZ_RESULTS_AT_LAST_ATTEMPT = 'lastAttempt'
const QUIZ_RESULTS_AT_DATE         = 'date'
const QUIZ_RESULTS_AT_NEVER        = 'never'

const QUIZ_RESULTS_AVAILABILITY = {
  [QUIZ_RESULTS_AT_VALIDATION]  : trans('at_the_end_of_assessment', {}, 'quiz'),
  [QUIZ_RESULTS_AT_LAST_ATTEMPT]: trans('after_the_last_attempt', {}, 'quiz'),
  [QUIZ_RESULTS_AT_DATE]        : trans('from', {}, 'quiz'),
  [QUIZ_RESULTS_AT_NEVER]       : trans('never', {}, 'quiz')
}


// Quiz score
const QUIZ_SCORE_AT_CORRECTION = 'correction'
const QUIZ_SCORE_AT_VALIDATION = 'validation'
const QUIZ_SCORE_AT_NEVER      = 'never'

const QUIZ_SCORE_AVAILABILITY = {
  [QUIZ_SCORE_AT_CORRECTION] : trans('at_the_same_time_that_the_correction', {}, 'quiz'),
  [QUIZ_SCORE_AT_VALIDATION] : trans('at_the_end_of_assessment', {}, 'quiz'),
  [QUIZ_SCORE_AT_NEVER]      : trans('never', {}, 'quiz')
}


// Quiz numbering
// TODO : maybe merge with path numbering
const NUMBERING_NONE    = 'none'
const NUMBERING_LITERAL = 'litteral'
const NUMBERING_NUMERIC = 'numeric'

const QUIZ_NUMBERINGS = {
  [NUMBERING_NONE]   : trans('quiz_numbering_none', {}, 'quiz'),
  [NUMBERING_NUMERIC]: trans('quiz_numbering_numeric', {}, 'quiz'),
  [NUMBERING_LITERAL]: trans('quiz_numbering_literal', {}, 'quiz')
}

// deprecated
const TOTAL_SCORE_ON_DEFAULT = 'default'
const TOTAL_SCORE_ON_CUSTOM = 'custom'

export const constants = {
  // shuffle
  SHUFFLE_NEVER,
  SHUFFLE_ALWAYS,
  SHUFFLE_ONCE,
  SHUFFLE_MODES,

  // picking
  QUIZ_PICKING_DEFAULT,
  QUIZ_PICKING_TAGS,
  QUIZ_PICKINGS,

  // results
  QUIZ_RESULTS_AT_VALIDATION,
  QUIZ_RESULTS_AT_LAST_ATTEMPT,
  QUIZ_RESULTS_AT_DATE,
  QUIZ_RESULTS_AT_NEVER,
  QUIZ_RESULTS_AVAILABILITY,

  // score
  QUIZ_SCORE_AT_CORRECTION,
  QUIZ_SCORE_AT_VALIDATION,
  QUIZ_SCORE_AT_NEVER,
  QUIZ_SCORE_AVAILABILITY,

  // numbering
  NUMBERING_NONE,
  NUMBERING_LITERAL,
  NUMBERING_NUMERIC,
  QUIZ_NUMBERINGS,

  TOTAL_SCORE_ON_DEFAULT,
  TOTAL_SCORE_ON_CUSTOM
}
