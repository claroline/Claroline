import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {matchPath, Routes, withRouter} from '#/main/app/router'
import {currentUser} from '#/main/core/user/current'

import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {PageActions, PageAction} from '#/main/core/layout/page'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

import {User}    from '#/main/core/administration/user/user/components/user'
import {Users}   from '#/main/core/workspace/user/user/components/users'

import {actions} from '#/main/core/workspace/user/user/actions'
import {ADMIN, getPermissionLevel} from  '#/main/core/workspace/user/restrictions'
import {select}  from '#/main/core/workspace/user/selectors'
import {getModalDefinition} from '#/main/core/workspace/user/role/modal'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {UserList} from '#/main/core/administration/user/user/components/user-list'

const UserTabActionsComponent = props =>
  <PageActions>
    {!matchPath(props.location.pathname, {path: '/users/form'}) &&
      <PageAction
        type={CALLBACK_BUTTON}
        label={trans('register_users')}
        icon="fa fa-plus"
        callback={() => props.register(props.workspace)}
        primary={true}
      />
    }

    {getPermissionLevel(currentUser(), props.workspace) === ADMIN &&
      <PageAction
        type={LINK_BUTTON}
        icon="fa fa-pencil"
        label={trans('create_user')}
        target={trans('create_user')}
      />
    }
  </PageActions>

UserTabActionsComponent.propTypes = {
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired,
  location: T.object,
  register: T.func
}

const UserTabActions = withRouter(connect(
  state => ({
    workspace: select.workspace(state)
  }),
  dispatch => ({
    register(workspace) {
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-user',
        title: trans('register_users'),
        subtitle: trans('workspace_register_select_users'),
        confirmText: trans('select', {}, 'actions'),
        name: 'users.picker',
        definition: UserList.definition,
        card: UserList.card,
        fetch: {
          url: ['apiv2_user_list_registerable'],
          autoload: true
        },
        handleSelect: (users) => {
          dispatch(modalActions.showModal(MODAL_DATA_LIST, getModalDefinition(
            'fa fa-fw fa-user',
            trans('register_users'),
            workspace,
            (roles) => roles.forEach(role => dispatch(actions.addUsersToRole(role, users)))
          )))
        }
      }))
    }
  })
)(UserTabActionsComponent))

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
        onEnter: (params) => props.openForm(params.id || null, props.collaboratorRole)
      }
    ]}
  />

UserTabComponent.propTypes = {
  openForm: T.func.isRequired,
  collaboratorRole: T.object
}

const UserTab = connect(
  state => ({
    collaboratorRole: select.collaboratorRole(state)
  }),
  dispatch => ({
    openForm(id = null, collaboratorRole) {
      dispatch(actions.open('users.current', id, {
        organization: null, // retrieve it with axel stuff
        roles: [collaboratorRole]
      }))
    }
  })
)(UserTabComponent)

export {
  UserTabActions,
  UserTab
}
