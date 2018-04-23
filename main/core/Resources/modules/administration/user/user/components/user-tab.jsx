import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t} from '#/main/core/translation'
import {matchPath, Routes, withRouter} from '#/main/core/router'

import {PageActions} from '#/main/core/layout/page/components/page-actions.jsx'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

import {User}    from '#/main/core/administration/user/user/components/user.jsx'
import {Users}   from '#/main/core/administration/user/user/components/users.jsx'
import {actions} from '#/main/core/administration/user/user/actions'

const UserTabActionsComponent = props =>
  <PageActions>
    <FormPageActionsContainer
      formName="users.current"
      target={(user, isNew) => isNew ?
        ['apiv2_user_create'] :
        ['apiv2_user_update', {id: user.id}]
      }
      opened={!!matchPath(props.location.pathname, {path: '/users/form'})}
      open={{
        type: 'link',
        icon: 'fa fa-plus',
        label: t('add_user'),
        target: '/users/form'
      }}
      cancel={{
        type: 'link',
        target: '/users',
        exact: true
      }}
    />
  </PageActions>

UserTabActionsComponent.propTypes = {
  location: T.shape({
    pathname: T.string
  }).isRequired
}

const UserTabActions = withRouter(UserTabActionsComponent)

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
        onEnter: (params) => props.openForm(params.id || null)
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
