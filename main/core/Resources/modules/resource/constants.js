import {t_res}        from '#/main/core/resource/translation'

const RESOURCE_GROUP_GENERIC    = 'generic'
const RESOURCE_GROUP_EVALUATION = 'evaluation'
const RESOURCE_GROUP_COMMUNITY  = 'community'
const RESOURCE_GROUP_CONTENT    = 'content'

const RESOURCE_GROUPS = {
  [RESOURCE_GROUP_GENERIC]: {
    icon: '',
    label: ''
  },
  [RESOURCE_GROUP_EVALUATION]: {
    icon: '',
    label: ''
  },
  [RESOURCE_GROUP_COMMUNITY]: {
    icon: '',
    label: ''
  },
  [RESOURCE_GROUP_CONTENT]: {
    icon: '',
    label: ''
  }
}

const RESOURCE_CLOSE_WS = 0
const RESOURCE_CLOSE_DESKTOP = 1

const RESOURCE_CLOSE_TARGETS = {
  [RESOURCE_CLOSE_WS]: t_res('close_redirect_workspace'),
  [RESOURCE_CLOSE_DESKTOP]: t_res('close_redirect_desktop')
}

export const constants = {
  RESOURCE_GROUP_GENERIC,
  RESOURCE_GROUP_EVALUATION,
  RESOURCE_GROUP_COMMUNITY,
  RESOURCE_GROUP_CONTENT,
  RESOURCE_GROUPS,
  RESOURCE_CLOSE_WS,
  RESOURCE_CLOSE_DESKTOP,
  RESOURCE_CLOSE_TARGETS
}
