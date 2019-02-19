import {trans} from '#/main/app/intl/translation'

// TODO : maybe merge with path numbering
const NUMBERING_NONE    = 'none'
const NUMBERING_LITERAL = 'litteral'
const NUMBERING_NUMERIC = 'numeric'

const QUIZ_NUMBERINGS = {
  [NUMBERING_NONE]   : trans('quiz_numbering_none', {}, 'quiz'),
  [NUMBERING_NUMERIC]: trans('quiz_numbering_numeric', {}, 'quiz'),
  [NUMBERING_LITERAL]: trans('quiz_numbering_literal', {}, 'quiz')
}

export const constants = {
  // numbering
  NUMBERING_NONE,
  NUMBERING_LITERAL,
  NUMBERING_NUMERIC,
  QUIZ_NUMBERINGS
}
