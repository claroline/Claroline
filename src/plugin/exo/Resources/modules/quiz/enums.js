import {trans} from '#/main/app/intl/translation'

// TODO : migrate select options formats

export const QUIZ_PICKING_DEFAULT = 'standard'
export const QUIZ_PICKING_TAGS = 'tags'

export const quizPicking = {
  [QUIZ_PICKING_DEFAULT]: trans('quiz_picking_steps', {}, 'quiz'),
  [QUIZ_PICKING_TAGS]: trans('quiz_picking_tags', {}, 'quiz')
}

export const SHUFFLE_NEVER = 'never'
export const SHUFFLE_ALWAYS = 'always'
export const SHUFFLE_ONCE = 'once'

export const shuffleModes = {
  [SHUFFLE_NEVER]: trans('never', {}, 'quiz'),
  [SHUFFLE_ALWAYS]: trans('at_each_attempt', {}, 'quiz'),
  [SHUFFLE_ONCE]: trans('at_first_attempt', {}, 'quiz')
}
export const SHOW_CORRECTION_AT_VALIDATION = 'validation'
export const SHOW_CORRECTION_AT_LAST_ATTEMPT = 'lastAttempt'
export const SHOW_CORRECTION_AT_DATE = 'date'
export const SHOW_CORRECTION_AT_NEVER = 'never'

export const correctionModes = [
  [SHOW_CORRECTION_AT_VALIDATION, 'at_the_end_of_assessment'],
  [SHOW_CORRECTION_AT_LAST_ATTEMPT, 'after_the_last_attempt'],
  [SHOW_CORRECTION_AT_DATE, 'from'],
  [SHOW_CORRECTION_AT_NEVER, 'never']
]

export const SHOW_SCORE_AT_CORRECTION = 'correction'
export const SHOW_SCORE_AT_VALIDATION = 'validation'
export const SHOW_SCORE_AT_NEVER = 'never'

export const markModes = [
  [SHOW_SCORE_AT_CORRECTION, 'at_the_same_time_that_the_correction'],
  [SHOW_SCORE_AT_VALIDATION, 'at_the_end_of_assessment'],
  [SHOW_SCORE_AT_NEVER, 'never']
]

export const SCORE_SUM = 'sum'
export const SCORE_FIXED = 'fixed'
export const SCORE_MANUAL = 'manual'
export const SCORE_RULES = 'rules'
