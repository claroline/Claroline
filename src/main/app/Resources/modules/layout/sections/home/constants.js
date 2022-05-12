import {trans} from '#/main/app/intl/translation'

const HOME_TYPE_NONE = 'none'
const HOME_TYPE_URL  = 'url'
const HOME_TYPE_HTML = 'html'
const HOME_TYPE_TOOL = 'tool'

const HOME_TYPES  = {
  [HOME_TYPE_NONE]: trans('platform_home_none'),
  [HOME_TYPE_URL]: trans('url'),
  [HOME_TYPE_HTML]: trans('html'),
  [HOME_TYPE_TOOL]: trans('platform_home_tool')
}

export const constants = {
  HOME_TYPES,

  HOME_TYPE_NONE,
  HOME_TYPE_URL,
  HOME_TYPE_HTML,
  HOME_TYPE_TOOL
}
