import {trans} from '#/main/core/translation'

// review types
const REVIEW_TYPE_MANAGER = 'manager'
const REVIEW_TYPE_PEER    = 'peer'

const REVIEW_TYPES = {
  [REVIEW_TYPE_MANAGER]: trans('manager_review', {}, 'dropzone'),
  [REVIEW_TYPE_PEER]:    trans('peer_review', {}, 'dropzone')
}

// planning
const PLANNING_TYPE_MANUAL = 'manual'
const PLANNING_TYPE_AUTO   = 'auto'

const PLANNING_TYPES = {
  [PLANNING_TYPE_MANUAL]: trans('planning_manual', {}, 'dropzone'),
  [PLANNING_TYPE_AUTO]:   trans('planning_auto', {}, 'dropzone')
}

const STATE_NOT_STARTED = 'not_started'
const STATE_ALLOW_DROP = 'drop'
const STATE_FINISHED = 'finished'
const STATE_PEER_REVIEW = 'review'
const STATE_ALLOW_DROP_AND_PEER_REVIEW = 'drop_review'
const STATE_WAITING_FOR_PEER_REVIEW = 'review_standby'

const PLANNING_STATES = {
  // planning for managers review
  [REVIEW_TYPE_MANAGER]: {
    [STATE_NOT_STARTED]: trans('state_not_started', {}, 'dropzone'),
    [STATE_ALLOW_DROP]:  trans('state_allow_drop', {}, 'dropzone'),
    [STATE_FINISHED]:    trans('state_finished', {}, 'dropzone')
  },
  // planning for peer review
  [REVIEW_TYPE_PEER]: {
    [STATE_NOT_STARTED]: trans('state_not_started', {}, 'dropzone'),
    [STATE_ALLOW_DROP]: trans('state_allow_drop', {}, 'dropzone'),
    [STATE_ALLOW_DROP_AND_PEER_REVIEW]: trans('state_allow_drop_and_peer_review', {}, 'dropzone'),
    [STATE_PEER_REVIEW]: trans('state_peer_review', {}, 'dropzone'),
    [STATE_FINISHED]: trans('state_finished', {}, 'dropzone')
  },
  // all planing states
  all: {
    [STATE_NOT_STARTED]: trans('state_not_started', {}, 'dropzone'),
    [STATE_ALLOW_DROP]: trans('state_allow_drop', {}, 'dropzone'),
    [STATE_ALLOW_DROP_AND_PEER_REVIEW]: trans('state_allow_drop_and_peer_review', {}, 'dropzone'),
    [STATE_WAITING_FOR_PEER_REVIEW]: trans('state_waiting_peer_review', {}, 'dropzone'),
    [STATE_PEER_REVIEW]: trans('state_peer_review', {}, 'dropzone'),
    [STATE_FINISHED]: trans('state_finished', {}, 'dropzone')
  }
}

// documents
const DOCUMENT_TYPE_FILE     = 'file'
const DOCUMENT_TYPE_TEXT     = 'html'
const DOCUMENT_TYPE_URL      = 'url'
const DOCUMENT_TYPE_RESOURCE = 'resource'

const DOCUMENT_TYPES = {
  [DOCUMENT_TYPE_FILE]:     trans('uploaded_files', {}, 'dropzone'),
  [DOCUMENT_TYPE_TEXT]:     trans('rich_text_online_edition', {}, 'dropzone'),
  [DOCUMENT_TYPE_URL]:      trans('url_info', {}, 'dropzone'),
  [DOCUMENT_TYPE_RESOURCE]: trans('workspace_resources', {}, 'dropzone')
}

// drops
const DROP_TYPE_USER = 'user'
const DROP_TYPE_TEAM = 'team'

const DROP_TYPES = {
  [DROP_TYPE_USER]: trans('drop_type_user', {}, 'dropzone'),
  [DROP_TYPE_TEAM]: trans('drop_type_team', {}, 'dropzone')
}

export const constants = {
  REVIEW_TYPE_MANAGER,
  REVIEW_TYPE_PEER,
  REVIEW_TYPES,
  PLANNING_TYPE_MANUAL,
  PLANNING_TYPE_AUTO,
  PLANNING_TYPES,
  STATE_NOT_STARTED,
  STATE_ALLOW_DROP,
  STATE_FINISHED,
  STATE_PEER_REVIEW,
  STATE_ALLOW_DROP_AND_PEER_REVIEW,
  STATE_WAITING_FOR_PEER_REVIEW,
  PLANNING_STATES,
  DROP_TYPE_USER,
  DROP_TYPE_TEAM,
  DROP_TYPES,
  DOCUMENT_TYPE_FILE,
  DOCUMENT_TYPE_TEXT,
  DOCUMENT_TYPE_URL,
  DOCUMENT_TYPE_RESOURCE,
  DOCUMENT_TYPES
}
