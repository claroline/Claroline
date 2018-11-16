import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

/**
 * Open workspace action.
 */
export default (workspaces) => ({
  name: 'open',
  type: URL_BUTTON,
  label: trans('open', {}, 'actions'),
  primary: true,
  icon: 'fa fa-fw fa-arrow-circle-o-right',
  target: ['claro_workspace_open', {
    workspaceId: workspaces[0].id
  }]
})
