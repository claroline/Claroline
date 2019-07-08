import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_WORKSPACE_PARAMETERS} from '#/main/core/workspace/modals/parameters'

// TODO : make it work everywhere (for now it only work in administration)

export default (workspaces) => ({
  name: 'configure',
  //type: LINK_BUTTON,
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-cog',
  label: trans('configure', {}, 'actions'),
  displayed: -1 !== workspaces.findIndex(workspace => hasPermission('administrate', workspace)),
  group: trans('management'),
  scope: ['object'],

  //target: `/desktop/workspaces/form/${workspaces[0].uuid}`,
  modal: [MODAL_WORKSPACE_PARAMETERS, {
    workspace: workspaces[0]
  }]
})
