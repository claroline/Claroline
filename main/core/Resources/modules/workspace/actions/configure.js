import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

// TODO : make it work everywhere (for now it only work in administration)

export default (workspaces) => ({
  name: 'configure',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-cog',
  label: trans('configure', {}, 'actions'),
  displayed: -1 !== workspaces.findIndex(workspace => hasPermission('administrate', workspace)),
  target: `/workspaces/form/${workspaces[0].uuid}`,
  group: trans('management'),
  scope: ['object']
})
