import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {LINK_BUTTON} from '#/main/app/buttons'
import {actions as listActions} from '#/main/app/content/list/store'
import {ListData} from '#/main/app/content/list/containers/data'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {trans} from '#/main/app/intl/translation'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {UserList, getUserListDefinition} from '#/main/core/administration/community/user/components/user-list'
import {getActions} from '#/main/core/user/utils'

// todo : restore custom actions the same way resource actions are implemented

const UsersList = props =>
  <ListData
    name={`${baseSelectors.STORE_NAME}.users.list`}
    fetch={{
      url: ['apiv2_user_list_managed_organization'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_user_delete_bulk']
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/users/form/${row.id}`,
      label: trans('edit', {}, 'actions')
    })}
    actions={(rows) => getActions(rows, {
      add: () => props.invalidateList(`${baseSelectors.STORE_NAME}.users.list`),
      update: () => props.invalidateList(`${baseSelectors.STORE_NAME}.users.list`),
      delete: () => props.invalidateList(`${baseSelectors.STORE_NAME}.users.list`)
    }, props.path, props.currentUser)}
    definition={getUserListDefinition({platformRoles: props.platformRoles})}
    card={UserList.card}
  />

UsersList.propTypes = {
  currentUser: T.object,
  path: T.string.isRequired,
  invalidateList: T.func.isRequired,
  platformRoles: T.array.isRequired
}

UsersList.defaultProps = {
  platformRoles: []
}

const Users = connect(
  state => ({
    currentUser: securitySelectors.currentUser(state),
    path: toolSelectors.path(state),
    platformRoles: baseSelectors.platformRoles(state)
  }),
  dispatch => ({
    invalidateList(name) {
      dispatch(listActions.invalidateData(name))
    }
  })
)(UsersList)

export {
  Users
}
