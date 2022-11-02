import React from 'react'
import {connect} from 'react-redux'

import {selectors as formSelectors} from '#/main/app/content/form/store'

import {actions} from '#/main/community/tools/community/role/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions as workspaceActions, selectors as workspaceSelectors} from '#/main/core/workspace/store'

import {Role as RoleComponent} from '#/main/community/tools/community/role/components/role'
import {selectors} from '#/main/community/tools/community/role/store'

const Role = connect(
  state => ({
    new: formSelectors.isNew(formSelectors.form(state, selectors.FORM_NAME)),
    role: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)),
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state) ? toolSelectors.contextData(state) : null,
    shortcuts: toolSelectors.contextData(state) ? workspaceSelectors.shortcuts(state) : null
  }),
  dispatch => ({
    reload(id, workspace) {
      dispatch(actions.open(selectors.FORM_NAME, id, {
        type: 2, // todo : ugly workspace type
        workspace: workspace
      }))
    },
    addUsers(roleId, selected) {
      dispatch(actions.addUsers(roleId, selected.map(row => row.id)))
    },
    addGroups(roleId, selected) {
      dispatch(actions.addGroups(roleId, selected.map(row => row.id)))
    },
    addShortcuts(workspaceId, roleId, shortcuts) {
      dispatch(workspaceActions.addShortcuts(workspaceId, roleId, shortcuts))
    },
    removeShortcut(workspaceId, roleId, type, name) {
      dispatch(workspaceActions.removeShortcut(workspaceId, roleId, type, name))
    }
  })
)(RoleComponent)

export {
  Role
}
