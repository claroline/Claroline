export const TYPE_QUIZ = 'quiz'
export const TYPE_STEP = 'step'

export const QUIZ_SUMMATIVE = 'summative'
export const QUIZ_EVALUATIVE = 'evaluative'
export const QUIZ_FORMATIVE = 'formative'

export const quizTypes = [
  [QUIZ_SUMMATIVE, 'summative'],
  [QUIZ_EVALUATIVE, 'evaluative'],
  [QUIZ_FORMATIVE, 'formative']
]

export const VIEW_OVERVIEW = 'overview'
export const VIEW_PLAYER = 'player'
export const VIEW_EDITOR = 'editor'
export const VIEW_PAPERS = 'papers'
export const VIEW_PAPER = 'paper'
export const VIEW_CORRECTION_QUESTIONS = 'correction_questions'
export const VIEW_CORRECTION_ANSWERS = 'correction_answers'

export const viewModes = [
  [VIEW_OVERVIEW, 'overview'],
  [VIEW_PLAYER, 'player'],
  [VIEW_EDITOR, 'editor'],
  [VIEW_PAPERS, 'papers'],
  [VIEW_PAPER, 'paper'],
  [VIEW_CORRECTION_QUESTIONS, 'correction_questions'],
  [VIEW_CORRECTION_ANSWERS, 'correction_answers']
]

export const SHUFFLE_NEVER = 'never'
export const SHUFFLE_ONCE = 'once'
export const SHUFFLE_ALWAYS = 'always'

export const shuffleModes = [
  [SHUFFLE_NEVER, 'never'],
  [SHUFFLE_ONCE, 'at_first_attempt'],
  [SHUFFLE_ALWAYS, 'at_each_attempt']
]

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
