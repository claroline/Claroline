import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/core/router'
import {User,  UserActions}  from '#/main/core/administration/user/user/components/user.jsx'
import {Users, UsersActions} from '#/main/core/administration/user/user/components/users.jsx'

import {actions} from '#/main/core/administration/user/user/actions'

const UserTabActions = () =>
  <Routes
    routes={[
      {
        path: '/users',
        exact: true,
        component: UsersActions
      }, {
        path: '/users/add',
        exact: true,
        component: UserActions
      }, {
        path: '/users/:id',
        component: UserActions
      }
    ]}
  />

const UserTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/users',
        exact: true,
        component: Users
      }, {
        path: '/users/add',
        exact: true,
        component: User,
        onEnter: () => props.openForm()
      }, {
        path: '/users/:id',
        component: User,
        onEnter: (params) => props.openForm(params.id)
      }
    ]}
  />

UserTabComponent.propTypes = {
  openForm: T.func.isRequired
}

const UserTab = connect(
  null,
  dispatch => ({
    openForm(id = null) {
      dispatch(actions.open('users.current', id))
    }
  })
)(UserTabComponent)

export {
  UserTabActions,
  UserTab
}
