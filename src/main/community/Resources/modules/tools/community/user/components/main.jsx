import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {UserList} from '#/main/community/tools/community/user/containers/list'
import {User} from '#/main/community/tools/community/user/components/user'
import {UserCreate} from '#/main/community/tools/community/user/containers/create'

const UserMain = props =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/users',
        component: UserList,
        exact: true
      }, {
        path: '/users/new',
        component: UserCreate,
        onEnter: props.new,
        disabled: 'desktop' !== props.contextType || !props.canRegister
      }, {
        path: '/users/form/:id?',
        component: User,
        onEnter: (params) => props.open(params.id || null)
      }
    ]}
  />

UserMain.propTypes = {
  path: T.string.isRequired,
  contextType: T.string.isRequired,
  canRegister: T.bool.isRequired,

  open: T.func.isRequired,
  new: T.func.isRequired
}

export {
  UserMain
}
