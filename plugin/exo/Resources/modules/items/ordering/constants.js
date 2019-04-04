import {trans} from '#/main/app/intl/translation'

const MODE_INSIDE = 'inside'
const MODE_BESIDE = 'beside'

const MODE_CHOICES = {
  [MODE_INSIDE]: trans('ordering_mode_inside', {}, 'quiz'),
  [MODE_BESIDE]: trans('ordering_mode_beside', {}, 'quiz')
}

const DIRECTION_VERTICAL = 'vertical'
const DIRECTION_HORIZONTAL = 'horizontal'

const DIRECTION_CHOICES = {
  [DIRECTION_VERTICAL]: trans('vertical', {}, 'quiz'),
  [DIRECTION_HORIZONTAL]: trans('horizontal', {}, 'quiz')
}

export const constants = {
  MODE_INSIDE,
  MODE_BESIDE,
  DIRECTION_VERTICAL,
  DIRECTION_HORIZONTAL,
  MODE_CHOICES,
  DIRECTION_CHOICES
}
