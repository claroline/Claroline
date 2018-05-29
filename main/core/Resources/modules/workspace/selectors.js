import {createSelector} from 'reselect'

const workspace = (state) => state.workspace
const tools = (state) => state.tools
const openedTool = (state) => state.openedTool

/**
 * Gets the list of roles which can access the WS.
 *
 * @return {array}
 */
const roles = createSelector(
  [workspace],
  (workspace) => workspace.roles
)

export const select = {
  roles,
  workspace,
  tools,
  openedTool
}
