import React from 'react'

import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {RoleList} from '#/main/core/administration/user/role/components/role-list.jsx'

const Roles = () =>
  <DataListContainer
    name="roles.list"
    fetch={{
      url: ['apiv2_role_list'],
      autoload: true
    }}
    primaryAction={RoleList.open}
    deleteAction={(rows) => ({
      type: 'url',
      target: ['apiv2_role_delete_bulk'],
      disabled: !!rows.find(role => role.meta.readOnly)
    })}
    definition={RoleList.definition}
    card={RoleList.card}
  />

export {
  Roles
}
