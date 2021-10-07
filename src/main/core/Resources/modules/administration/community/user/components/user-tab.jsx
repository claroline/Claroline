import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {User} from '#/main/core/administration/community/user/components/user'
import {Users} from '#/main/core/administration/community/user/components/users'
import {UsersMerge} from '#/main/core/administration/community/user/components/users-merge'
import {actions, selectors} from '#/main/core/administration/community/user/store'
import {MODAL_USER_DISABLE_INACTIVE} from '#/main/core/administration/community/user/modals/disable-inactive'

const UserTabComponent = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('users'),
      target: `${props.path}/users`
    }]}
    subtitle={trans('users')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_user'),
        target: `${props.path}/users/form`,
        primary: true,
        disabled: props.limitReached
      }, {
        name: 'disable-inactive',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-user-slash',
        label: trans('disable_inactive_users'),
        modal: [MODAL_USER_DISABLE_INACTIVE],
        dangerous: true
      }
    ]}
  >
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
  </ToolPage>

UserTabComponent.propTypes = {
  path: T.string.isRequired,
  limitReached: T.bool.isRequired,
  openForm: T.func.isRequired,
  closeForm: T.func.isRequired,
  compare: T.func.isRequired
}

const UserTab = connect(
  (state) => ({
    path: toolSelectors.path(state),
    limitReached: selectors.limitReached(state)
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
  UserTab
}
