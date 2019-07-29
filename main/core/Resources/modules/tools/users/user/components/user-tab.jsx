import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/app/router'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/core/tools/users/store'
import {actions} from '#/main/core/tools/users/user/store'
import {Users} from '#/main/core/tools/users/user/components/users'
import {User} from '#/main/core/tools/users/user/components/user'

const UserTabComponent = props =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/users',
        exact: true,
        component: Users
      }, {
        path: '/users/form/:id?',
        component: User,
        onEnter: (params) => props.openForm(params.id || null, props.collaboratorRole)
      }
    ]}
  />

UserTabComponent.propTypes = {
  path: T.string.isRequired,
  openForm: T.func.isRequired,
  collaboratorRole: T.object
}

const UserTab = connect(
  state => ({
    path: toolSelectors.path(state),
    collaboratorRole: toolSelectors.contextData(state).roles.find(role => role.translationKey === 'collaborator')
  }),
  dispatch => ({
    openForm(id = null, collaboratorRole) {
      dispatch(actions.open(selectors.STORE_NAME + '.users.current', id, {
        organization: null, // retrieve it with axel stuff
        roles: [collaboratorRole]
      }))
    }
  })
)(UserTabComponent)

export {
  UserTab
}
