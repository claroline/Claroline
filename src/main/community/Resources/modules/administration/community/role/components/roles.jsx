import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as baseSelectors} from '#/main/community/administration/community/store'
import {RoleList} from '#/main/community/administration/community/role/components/role-list'

const RolesList = (props) =>
  <ListData
    name={`${baseSelectors.STORE_NAME}.roles.list`}
    fetch={{
      url: ['apiv2_role_list'],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/roles/form/${row.id}`,
      label: trans('edit', {}, 'actions')
    })}
    delete={{
      url: ['apiv2_role_delete_bulk'],
      disabled: (rows) => !!rows.find(role => role.meta.readOnly)
    }}
    definition={RoleList.definition}
    card={RoleList.card}
  />

RolesList.propTypes = {
  path: T.string.isRequired
}

const Roles = connect(
  (state) => ({
    path: toolSelectors.path(state)
  })
)(RolesList)

export {
  Roles
}
