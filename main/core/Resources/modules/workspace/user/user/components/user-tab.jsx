import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {navigate, matchPath, Routes, withRouter} from '#/main/core/router'
import {currentUser} from '#/main/core/user/current'
import {ADMIN, getPermissionLevel} from  '#/main/core/workspace/user/restrictions'

import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

import {User}    from '#/main/core/administration/user/user/components/user.jsx'
import {Users}   from '#/main/core/workspace/user/user/components/users.jsx'

import {actions} from '#/main/core/workspace/user/user/actions'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {PageActions} from '#/main/core/layout/page/components/page-actions.jsx'
import {PageAction} from '#/main/core/layout/page'
import {select}  from '#/main/core/workspace/user/selectors'
import {getModalDefinition} from '#/main/core/workspace/user/role/modal'
//we have an issue because we a have to use the same definition for both selection modal.
//grouplist only displays name so it's ok as a workaround
import {GroupList} from '#/main/core/administration/user/group/components/group-list.jsx'
import {MODAL_DATA_PICKER} from '#/main/core/data/list/modals'

const UserTabActionsComponent = props => {
  return(
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
            icon: 'fa fa-plus',
            label: trans('create_user'),
            action: '#/users/form'
          }}
          cancel={{
            action: () => navigate('/users')
          }}
        />
      }
      <PageAction
        id='add-role'
        title={trans('register_users')}
        icon={'fa fa-id-badge'}
        disabled={false}
        action={() => props.register(props.workspace)}
        primary={false}
      />
    </PageActions>
  )
}

UserTabActionsComponent.propTypes = {
  workspace: T.object,
  location: T.object,
  register: T.func
}

const ConnectedActions = connect(
  state => ({
    workspace: select.workspace(state)
  }),
  dispatch => ({
    register(workspace) {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-user',
        title: trans('add_user'),
        confirmText: trans('add'),
        name: 'users.picker',
        //only the name goes here
        definition: GroupList.definition,
        card: GroupList.card,
        fetch: {
          url: ['apiv2_user_list_registerable'],
          autoload: true
        },
        handleSelect: (users) => {
          dispatch(modalActions.showModal(MODAL_DATA_PICKER, getModalDefinition(
            workspace,
            (roles) => roles.forEach(role => dispatch(actions.addUsersToRole(role, users)))
          )))
        }
      }))
    }
  })
)(UserTabActionsComponent)

const UserTabActions = withRouter(ConnectedActions)

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
        onEnter: (params) => props.openForm(
          params.id || null,
          props.workspace,
          props.restrictions,
          props.collaboratorRole
        )
      }
    ]}
  />

UserTabComponent.propTypes = {
  openForm: T.func.isRequired,
  workspace: T.object,
  restrictions: T.object,
  collaboratorRole: T.object
}

const UserTab = connect(
  state => ({
    workspace: select.workspace(state),
    restrictions: select.restrictions(state),
    collaboratorRole: select.collaboratorRole(state)
  }),
  dispatch => ({
    openForm(id = null, workspace, restrictions, collaboratorRole) {

      const defaultValue = {
        organization: null, //retreive it with axel stuff
        roles: [collaboratorRole]
      }

      dispatch(actions.open('users.current', id, defaultValue))
    }
  })
)(UserTabComponent)

export {
  UserTabActions,
  UserTab
}
