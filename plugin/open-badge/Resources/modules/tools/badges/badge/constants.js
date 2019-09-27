import {trans} from '#/main/app/intl/translation'

const ISSUING_MODE_ORGANIZATION = 'organization'
const ISSUING_MODE_USER = 'user'
const ISSUING_MODE_GROUP = 'group'
const ISSUING_MODE_PEER = 'peer'
const ISSUING_MODE_WORKSPACE = 'workspace'

const ISSUING_MODES =  {
  [ISSUING_MODE_ORGANIZATION]: trans('issuing_mode_organization', {}, 'badge'),
  [ISSUING_MODE_USER]: trans('issuing_mode_user', {}, 'badge'),
  [ISSUING_MODE_GROUP]: trans('issuing_mode_group', {}, 'badge'),
  [ISSUING_MODE_PEER]: trans('issuing_mode_peer', {}, 'badge'),
  [ISSUING_MODE_WORKSPACE]: trans('issuing_mode_workspace', {}, 'badge')
}

export const constants = {
  ISSUING_MODES,
  ISSUING_MODE_ORGANIZATION,
  ISSUING_MODE_USER,
  ISSUING_MODE_GROUP,
  ISSUING_MODE_PEER,
  ISSUING_MODE_WORKSPACE
}
