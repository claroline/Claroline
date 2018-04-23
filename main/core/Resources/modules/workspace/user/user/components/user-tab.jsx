import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {matchPath, Routes, withRouter} from '#/main/core/router'
import {currentUser} from '#/main/core/user/current'

import {MODAL_DATA_PICKER} from '#/main/core/data/list/modals'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {PageActions, PageAction} from '#/main/core/layout/page'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

import {User}    from '#/main/core/administration/user/user/components/user.jsx'
import {Users}   from '#/main/core/workspace/user/user/components/users.jsx'

import {actions} from '#/main/core/workspace/user/user/actions'
import {ADMIN, getPermissionLevel} from  '#/main/core/workspace/user/restrictions'
import {select}  from '#/main/core/workspace/user/selectors'
import {getModalDefinition} from '#/main/core/workspace/user/role/modal'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {UserList} from '#/main/core/administration/user/user/components/user-list.jsx'

const UserTabActionsComponent = props =>
  <PageActions>
    {getPermissionLevel(currentUser(), props.workspace) === ADMIN &&
      <FormPageActionsContainer
        formName="users.current"
        target={(user, isNew) => isNew ?
          ['apiv2_user_create'] :
          ['apiv2_user_update', {id: user.id}]
        }
        opened={!!matchPath(props.location.pathname, {path: '/users/form'})}
        open={{
          type: 'link',
          label: trans('create_user'),
          target: '/users/form',
          primary: false
        }}
        cancel={{
          type: 'link',
          target: '/users',
          exact: true
        }}
      />
    }

    {!matchPath(props.location.pathname, {path: '/users/form'}) &&
      <PageAction
        id="add-role"
        type="callback"
        label={trans('register_users')}
        icon="fa fa-plus"
        action={() => props.register(props.workspace)}
        primary={true}
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
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
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
          dispatch(modalActions.showModal(MODAL_DATA_PICKER, getModalDefinition(
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
