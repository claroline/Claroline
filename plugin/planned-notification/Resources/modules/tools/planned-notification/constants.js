import {trans} from '#/main/core/translation'

const WORKSPACE_REGISTRATION_USER = 'workspace-role-subscribe_user'
const WORKSPACE_REGISTRATION_GROUP = 'workspace-role-subscribe_group'

const TRIGGERING_ACTIONS = {
  [WORKSPACE_REGISTRATION_USER]: trans('workspace-role-subscribe_user', {}, 'planned_notification'),
  [WORKSPACE_REGISTRATION_GROUP]: trans('workspace-role-subscribe_group', {}, 'planned_notification')
}

export const constants = {
  WORKSPACE_REGISTRATION_USER,
  WORKSPACE_REGISTRATION_GROUP,
  TRIGGERING_ACTIONS
}
