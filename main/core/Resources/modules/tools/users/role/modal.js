import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/main/core/tools/users/store'
import {RoleList} from '#/main/core/tools/users/role/components/role-list'

const getModalDefinition = (icon, title, workspace, actions) => ({
  icon: icon,
  title: title,
  subtitle: trans('workspace_register_select_roles'),
  confirmText: trans('register', {}, 'actions'),
  name: selectors.STORE_NAME + '.roles.workspacePicker',
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
