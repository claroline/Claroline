import {RoleList} from '#/main/core/administration/user/role/components/role-list.jsx'
import {trans} from '#/main/core/translation'
import {generateUrl} from '#/main/core/api/router'

export const getModalDefinition = (workspace, actions) => {
  return {
    icon: 'fa fa-fw fa-buildings',
    title: trans('add_roles'),
    confirmText: trans('add'),
    name: 'roles.workspacePicker',
    definition: RoleList.definition,
    card: RoleList.card,
    fetch: {
      url: generateUrl('apiv2_workspace_list_roles', {id: workspace.uuid}),
      autoload: true
    },
    handleSelect: (roles) => {
      actions(roles)
    }
  }
}
