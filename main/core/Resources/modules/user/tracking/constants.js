
const TRACKING_EVENT_GENERIC    = 'generic'
const TRACKING_EVENT_EVALUATION = 'evaluation'
const TRACKING_EVENT_COMMUNITY  = 'community'
const TRACKING_EVENT_CONTENT    = 'content'
const TRACKING_EVENT_BADGE      = 'badge'

const TRACKING_EVENTS = {
  [TRACKING_EVENT_GENERIC]: {
    icon: 'fa fa-flash'
  },
  [TRACKING_EVENT_EVALUATION]: {
    icon: 'fa fa-check-square-o'
  },
  [TRACKING_EVENT_COMMUNITY]: {
    icon: 'fa fa-users'
  },
  [TRACKING_EVENT_CONTENT]: {
    icon: 'fa fa-eye'
  },
  [TRACKING_EVENT_BADGE]: {
    icon: 'fa fa-trophy'
  }
}

export const constants = {
  TRACKING_EVENTS,
  TRACKING_EVENT_GENERIC,
  TRACKING_EVENT_EVALUATION,
  TRACKING_EVENT_COMMUNITY,
  TRACKING_EVENT_CONTENT,
  TRACKING_EVENT_BADGE
}
