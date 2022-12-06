import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions as workspaceActions, selectors as workspaceSelectors} from '#/main/core/workspace/store'

import {RoleShortcuts as RoleShortcutsComponent} from '#/main/community/tools/community/role/components/shortcuts'

const RoleShortcuts = connect(
  (state) => ({
    workspace: toolSelectors.contextData(state) ? toolSelectors.contextData(state) : null,
    tools: toolSelectors.contextData(state) ? workspaceSelectors.tools(state) : null,
    shortcuts: toolSelectors.contextData(state) ? workspaceSelectors.shortcuts(state) : null
  }),
  (dispatch) => ({
    addShortcuts(workspaceId, roleId, shortcuts) {
      dispatch(workspaceActions.addShortcuts(workspaceId, roleId, shortcuts))
    },
    removeShortcut(workspaceId, roleId, type, name) {
      dispatch(workspaceActions.removeShortcut(workspaceId, roleId, type, name))
    }
  })
)(RoleShortcutsComponent)

export {
  RoleShortcuts
}
