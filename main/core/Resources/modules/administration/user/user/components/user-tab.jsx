import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {Routes} from '#/main/app/router'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'

import {User}       from '#/main/core/administration/user/user/components/user.jsx'
import {Users}      from '#/main/core/administration/user/user/components/users.jsx'
import {UsersMerge} from '#/main/core/administration/user/user/components/users-merge'
import {actions}    from '#/main/core/administration/user/user/actions'

const UserTabActions = () =>
  <PageActions>
    <PageAction
      type="link"
      icon="fa fa-plus"
      label={trans('add_user')}
      target="/users/form"
      primary={true}
    />
  </PageActions>

const UserTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/users',
        exact: true,
        component: Users
      }, {
        path: '/users/form/:id?',
        component: User,
        onEnter: (params) => props.openForm('users.current', params.id || null)
      }, {
        path: '/users/merge/:id1/:id2',
        component: UsersMerge,
        onEnter: (params) => props.compare([params.id1, params.id2])
      }
    ]}
  />

UserTabComponent.propTypes = {
  openForm: T.func.isRequired,
  compare: T.func.isRequired
}

const UserTab = connect(
  null,
  dispatch => ({
    openForm(formName, id = null) {
      dispatch(actions.open(formName, id))
    },
    compare(userIds) {
      dispatch(actions.compare(userIds))
    }
  })
)(UserTabComponent)

export {
  UserTabActions,
  UserTab
}
