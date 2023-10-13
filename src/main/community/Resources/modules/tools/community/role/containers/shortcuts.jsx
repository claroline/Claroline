import {connect} from 'react-redux'

import {selectors as contextSelectors} from '#/main/app/context/store'

import {actions} from '#/main/community/tools/community/role/store'
import {RoleShortcuts as RoleShortcutsComponent} from '#/main/community/tools/community/role/components/shortcuts'

const RoleShortcuts = connect(
  (state) => ({
    workspace: contextSelectors.data(state),
    tools: contextSelectors.tools(state),
    shortcuts: contextSelectors.allShortcuts(state)
  }),
  (dispatch) => ({
    addShortcuts(workspaceId, roleId, shortcuts) {
      dispatch(actions.addShortcuts(workspaceId, roleId, shortcuts))
    },
    removeShortcut(workspaceId, roleId, type, name) {
      dispatch(actions.removeShortcut(workspaceId, roleId, type, name))
    }
  })
)(RoleShortcutsComponent)

export {
  RoleShortcuts
}
