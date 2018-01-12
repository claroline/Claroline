import React from 'react'

import {t} from '#/main/core/translation'

import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions.jsx'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {RoleList} from '#/main/core/administration/user/role/components/role-list.jsx'

const RolesActions = () =>
  <PageActions>
    <PageAction
      id="role-add"
      icon="fa fa-plus"
      title={t('add_role')}
      action="#/roles/add"
      primary={true}
    />
  </PageActions>

const Roles = () =>
  <DataListContainer
    name="roles.list"
    open={RoleList.open}
    fetch={{
      url: ['apiv2_role_list'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_role_delete_bulk'],
      disabled: (rows) => !!rows.find(role => role.meta.readOnly)
    }}
    definition={RoleList.definition}
    card={RoleList.card}
  />

export {
  RolesActions,
  Roles
}
