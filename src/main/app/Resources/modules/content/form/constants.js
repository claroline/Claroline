import {trans} from '#/main/app/intl/translation'

const FORM_MODE_SIMPLE   = 'simple'
const FORM_MODE_STANDARD = 'standard'
const FORM_MODE_ADVANCED = 'advanced'
const FORM_MODE_EXPERT   = 'expert'

const FORM_MODE_DEFAULT = FORM_MODE_EXPERT

const FORM_MODES = {
  [FORM_MODE_SIMPLE]: trans('simple'),
  [FORM_MODE_STANDARD]: trans('standard'),
  [FORM_MODE_ADVANCED]: trans('advanced'),
  [FORM_MODE_EXPERT]: trans('expert')
}

export const constants = {
  FORM_MODE_DEFAULT,
  FORM_MODES,
  FORM_MODE_SIMPLE,
  FORM_MODE_STANDARD,
  FORM_MODE_ADVANCED,
  FORM_MODE_EXPERT
}
