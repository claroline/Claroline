import {trans} from '#/main/app/intl/translation'

const CHOICE_TYPE_MULTIPLE = 'multiple'
const CHOICE_TYPE_SINGLE   = 'single'

const CHOICE_TYPES = {
  [CHOICE_TYPE_SINGLE]: trans('choice_single_answer', {}, 'quiz'),
  [CHOICE_TYPE_MULTIPLE]: trans('choice_multiple_answers', {}, 'quiz')
}

export const constants = {
  CHOICE_TYPES,

  CHOICE_TYPE_MULTIPLE,
  CHOICE_TYPE_SINGLE
}
