import {createSelector} from 'reselect'

const workspace = state => state.workspace

const tools = state => state.tools

const defaultOpening = createSelector(
  [workspace, tools],
  (workspace, tools) => {
    let defaultTool = null
    if ('resource' === workspace.opening.type) {
      defaultTool = `resources/${workspace.opening.target.id || ''}`
    } else if ('tool' === workspace.opening.type) {
      defaultTool = workspace.opening.target
    }

    // no default configured (or not properly)
    if (!defaultTool && tools[0]) {
      // open the first available tool
      defaultTool = tools[0].name
    }

    return defaultTool
  }
)

export const selectors = {
  workspace,
  tools,
  defaultOpening
}