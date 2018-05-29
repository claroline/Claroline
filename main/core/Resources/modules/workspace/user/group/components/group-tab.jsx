import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {matchPath, Routes, withRouter} from '#/main/app/router'
import {currentUser} from '#/main/core/user/current'

import {MODAL_DATA_PICKER} from '#/main/core/data/list/modals'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {PageActions, PageAction} from '#/main/core/layout/page'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

import {GroupList} from '#/main/core/administration/user/group/components/group-list.jsx'
import {Group}     from '#/main/core/administration/user/group/components/group.jsx'
import {Groups}    from '#/main/core/workspace/user/group/components/groups.jsx'
import {actions}   from '#/main/core/workspace/user/group/actions'
import {select}    from '#/main/core/workspace/user/selectors'

import {ADMIN, getPermissionLevel} from '#/main/core/workspace/user/restrictions'
import {getModalDefinition} from '#/main/core/workspace/user/role/modal'

const GroupTabActionsComponent = props =>
  <PageActions>
    {getPermissionLevel(currentUser(), props.workspace) === ADMIN &&
      <FormPageActionsContainer
        formName="groups.current"
        target={(user, isNew) => isNew ?
          ['apiv2_group_create'] :
          ['apiv2_group_update', {id: user.id}]
        }
        opened={!!matchPath(props.location.pathname, {path: '/groups/form'})}
        open={{
          type: 'link',
          label: trans('create_group'),
          target: '/groups/form',
          primary: false
        }}
        cancel={{
          type: 'link',
          target: '/groups',
          exact: true
        }}
      />
    }

    {!matchPath(props.location.pathname, {path: '/groups/form'}) &&
      <PageAction
        id="add-role"
        type="callback"
        label={trans('register_groups')}
        icon="fa fa-plus"
        callback={() => props.register(props.workspace)}
        primary={true}
      />
    }
  </PageActions>

GroupTabActionsComponent.propTypes = {
  location: T.shape({
    pathname: T.string
  }).isRequired,
  workspace: T.object,
  register: T.func.isRequired
}

const ConnectedActions = connect(
  state => ({
    workspace: select.workspace(state)
  }),
  dispatch => ({
    register(workspace) {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-users',
        title: trans('register_groups'),
        subtitle: trans('workspace_register_select_groups'),
        confirmText: trans('select', {}, 'actions'),
        name: 'groups.picker',
        definition: GroupList.definition,
        card: GroupList.card,
        fetch: {
          url: ['apiv2_group_list_registerable'],
          autoload: true
        },
        handleSelect: (groups) => {
          dispatch(modalActions.showModal(MODAL_DATA_PICKER, getModalDefinition(
            'fa fa-fw fa-users',
            trans('register_groups'),
            workspace,
            (roles) => roles.forEach(role => dispatch(actions.addGroupsToRole(role, groups)))
          )))
        }
      }))
    }
  })
)(GroupTabActionsComponent)

const GroupTabActions = withRouter(ConnectedActions)

const GroupTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/groups',
        exact: true,
        component: Groups
      }, {
        path: '/groups/form/:id?',
        component: Group,
        onEnter: (params) => props.openForm(params.id || null, props.collaboratorRole)
      }
    ]}
  />

GroupTabComponent.propTypes = {
  openForm: T.func.isRequired,
  collaboratorRole: T.object
}

const GroupTab = connect(
  state => ({
    collaboratorRole: select.collaboratorRole(state)
  }),
  dispatch => ({
    openForm(id = null, collaboratorRole) {

      const defaultValue = {
        organization: null, // retrieve it with axel stuff
        roles: [collaboratorRole]
      }

      dispatch(actions.open('groups.current', id, defaultValue))
    }
  })
)(GroupTabComponent)

export {
  GroupTabActions,
  GroupTab
}
