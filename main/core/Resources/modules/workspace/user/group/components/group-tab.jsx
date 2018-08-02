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

import {GroupList} from '#/main/core/administration/user/group/components/group-list'
import {Group}     from '#/main/core/administration/user/group/components/group'
import {Groups}    from '#/main/core/workspace/user/group/components/groups'
import {actions}   from '#/main/core/workspace/user/group/actions'
import {select}    from '#/main/core/workspace/user/selectors'

import {ADMIN, getPermissionLevel} from '#/main/core/workspace/user/restrictions'
import {getModalDefinition} from '#/main/core/workspace/user/role/modal'

const GroupTabActionsComponent = props =>
  <PageActions>
    {!matchPath(props.location.pathname, {path: '/groups/form'}) &&
      <PageAction
        type={CALLBACK_BUTTON}
        icon="fa fa-plus"
        label={trans('register_groups')}
        callback={() => props.register(props.workspace)}
        primary={true}
      />
    }

    {getPermissionLevel(currentUser(), props.workspace) === ADMIN &&
      <PageAction
        type={LINK_BUTTON}
        icon="fa fa-pencil"
        label={trans('create_group')}
        target="/groups/form"
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
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
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
          dispatch(modalActions.showModal(MODAL_DATA_LIST, getModalDefinition(
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
