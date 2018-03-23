import {trans} from '#/main/core/translation'

import {RoleList} from '#/main/core/workspace/user/role/components/role-list.jsx'

const getModalDefinition = (icon, title, workspace, actions) => ({
  icon: icon,
  title: title,
  subtitle: trans('workspace_register_select_roles'),
  confirmText: trans('register', {}, 'actions'),
  name: 'roles.workspacePicker',
  definition: RoleList.definition,
  card: RoleList.card,
  fetch: {
    url: ['apiv2_workspace_list_roles', {id: workspace.uuid}],
    autoload: true
  },
  handleSelect: (roles) => actions(roles)
})

export {
  getModalDefinition
}
