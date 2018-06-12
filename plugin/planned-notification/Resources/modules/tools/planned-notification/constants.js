import {trans} from '#/main/core/translation'

const WORKSPACE_REGISTRATION_USER = 'workspace-role-subscribe_user'
const WORKSPACE_REGISTRATION_GROUP = 'workspace-role-subscribe_group'
const WORKSPACE_FIRST_CONNECTION = 'workspace-enter'

const TRIGGERING_ACTIONS = {
  [WORKSPACE_REGISTRATION_USER]: trans('workspace-role-subscribe_user', {}, 'planned_notification'),
  [WORKSPACE_REGISTRATION_GROUP]: trans('workspace-role-subscribe_group', {}, 'planned_notification'),
  [WORKSPACE_FIRST_CONNECTION]: trans('workspace-enter', {}, 'planned_notification')
}

export {
  WORKSPACE_REGISTRATION_USER,
  WORKSPACE_REGISTRATION_GROUP,
  WORKSPACE_FIRST_CONNECTION,
  TRIGGERING_ACTIONS
}
