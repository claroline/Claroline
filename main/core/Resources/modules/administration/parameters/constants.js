import {trans} from '#/main/app/intl/translation'

const MESSAGE_TYPE_ONCE = 'once'
const MESSAGE_TYPE_ALWAYS = 'always'
const MESSAGE_TYPE_DISCARD = 'discard'

const MESSAGE_TYPES = {
  [MESSAGE_TYPE_ONCE]: trans('once'),
  [MESSAGE_TYPE_ALWAYS]: trans('always'),
  [MESSAGE_TYPE_DISCARD]: trans('remember_user_choice')
}

const ICON_SET_TYPE_RESOURCE = 'resource_icon_set'

export const constants = {
  MESSAGE_TYPE_ONCE,
  MESSAGE_TYPE_ALWAYS,
  MESSAGE_TYPE_DISCARD,
  MESSAGE_TYPES,
  ICON_SET_TYPE_RESOURCE
}