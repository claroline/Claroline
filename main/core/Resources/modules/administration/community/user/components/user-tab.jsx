import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'
import {LINK_BUTTON} from '#/main/app/buttons'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {User}       from '#/main/core/administration/community/user/components/user'
import {Users}      from '#/main/core/administration/community/user/components/users'
import {UsersMerge} from '#/main/core/administration/community/user/components/users-merge'
import {actions}    from '#/main/core/administration/community/user/store'

const UserTabActionsComponent = (props) =>
  <PageActions>
    <PageAction
      type={LINK_BUTTON}
      icon="fa fa-plus"
      label={trans('add_user')}
      target={`${props.path}/users/form`}
      primary={true}
    />
  </PageActions>

UserTabActionsComponent.propTypes = {
  path: T.string.isRequired
}

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
        onEnter: (params) => props.openForm(params.id || null),
        onLeave: props.closeForm
      }, {
        path: '/users/merge/:id1/:id2',
        component: UsersMerge,
        onEnter: (params) => props.compare([params.id1, params.id2])
      }
    ]}
  />

UserTabComponent.propTypes = {
  path: T.string.isRequired,
  openForm: T.func.isRequired,
  closeForm: T.func.isRequired,
  compare: T.func.isRequired
}

const UserTabActions = connect(
  (state) => ({
    path: toolSelectors.path(state)
  })
)(UserTabActionsComponent)

const UserTab = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  dispatch => ({
    openForm(id = null) {
      dispatch(actions.open(baseSelectors.STORE_NAME+'.users.current', id))
    },
    closeForm() {
      dispatch(actions.close(baseSelectors.STORE_NAME+'.users.current'))
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
