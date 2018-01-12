
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

export const RESOURCE_CLOSE_WS = 0
export const RESOURCE_CLOSE_DESKTOP = 1

export const closeTargets = [
  [RESOURCE_CLOSE_WS, 'close_redirect_workspace'],
  [RESOURCE_CLOSE_DESKTOP, 'close_redirect_desktop']
]

export const constants = {
  RESOURCE_GROUPS,
  RESOURCE_GROUP_GENERIC,
  RESOURCE_GROUP_EVALUATION,
  RESOURCE_GROUP_COMMUNITY,
  RESOURCE_GROUP_CONTENT
}