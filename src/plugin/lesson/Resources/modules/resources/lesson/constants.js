import {trans} from '#/main/app/intl'

const CREATE_CHAPTER = 'create'
const EDIT_CHAPTER = 'edit'
const VIEW_CHAPTER = 'view'

const NUMBERING_NONE    = 'none'
const NUMBERING_NUMERIC = 'numeric'
const NUMBERING_LITERAL = 'literal'
const NUMBERING_CUSTOM  = 'custom'

const LESSON_NUMBERINGS = {
  [NUMBERING_NONE]: trans('lesson_numbering_none', {}, 'lesson'),
  [NUMBERING_NUMERIC]: trans('lesson_numbering_numeric', {}, 'lesson'),
  [NUMBERING_LITERAL]: trans('lesson_numbering_literal', {}, 'lesson'),
  [NUMBERING_CUSTOM]: trans('lesson_numbering_custom', {}, 'lesson')
}

export const constants = {
  CREATE_CHAPTER,
  EDIT_CHAPTER,
  VIEW_CHAPTER,
  LESSON_NUMBERINGS,
  NUMBERING_NONE,
  NUMBERING_NUMERIC,
  NUMBERING_LITERAL,
  NUMBERING_CUSTOM
}
