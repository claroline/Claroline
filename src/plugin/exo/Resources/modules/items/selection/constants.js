import {trans} from '#/main/app/intl/translation'

const MODE_SELECT = 'select'
const MODE_FIND = 'find'
const MODE_HIGHLIGHT = 'highlight'

const MODE_CHOICES = {
  [MODE_SELECT]: trans('select', {}, 'quiz'),
  [MODE_FIND]: trans('find', {}, 'quiz'),
  [MODE_HIGHLIGHT]: trans('highlight', {}, 'quiz')
}

export const constants = {
  MODE_SELECT,
  MODE_FIND,
  MODE_HIGHLIGHT,
  MODE_CHOICES
}