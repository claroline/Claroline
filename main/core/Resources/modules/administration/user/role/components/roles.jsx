import React from 'react'

import {ListData} from '#/main/app/content/list/containers/data'
import {RoleList} from '#/main/core/administration/user/role/components/role-list'

const Roles = () =>
  <ListData
    name="roles.list"
    fetch={{
      url: ['apiv2_role_list'],
      autoload: true
    }}
    primaryAction={RoleList.open}
    delete={{
      url: ['apiv2_role_delete_bulk'],
      disabled: (rows) => !!rows.find(role => role.meta.readOnly)
    }}
    definition={RoleList.definition}
    card={RoleList.card}
  />

export {
  Roles
}
