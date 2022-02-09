import {createSelector} from 'reselect'
import isEmpty from 'lodash/isEmpty'
import uniqWith from 'lodash/uniqWith'

import {selectors as securitySelectors} from '#/main/app/security/store/selectors'
import {hasRole} from '#/main/app/security/permissions'

const STORE_NAME = 'workspace'

const store = (state) => state[STORE_NAME] || {}

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const notFound = createSelector(
  [store],
  (store) => store.notFound
)

const impersonated = createSelector(
  [store],
  (store) => store.impersonated
)

const roles = createSelector(
  [store],
  (store) => store.roles
)

const workspace = createSelector(
  [store],
  (store) => store.workspace
)

const managed = createSelector(
  [store],
  (store) => store.managed
)

const tools = createSelector(
  [store],
  (store) => store.tools
)

const root = createSelector(
  [store],
  (store) => store.root
)

// all the shortcuts defined in the workspace
const shortcuts = createSelector(
  [store],
  (store) => store.shortcuts
)

const userEvaluation = createSelector(
  [store],
  (store) => store.userEvaluation
)

// the current user enabled shortcuts
const userShortcuts = createSelector(
  [shortcuts, securitySelectors.currentUser],
  (shortcuts, currentUser) => {
    let definedShortcuts = []
    shortcuts.map(shortcut => {
      if (hasRole(shortcut.role.name, currentUser)) {
        definedShortcuts = definedShortcuts.concat(shortcut.data)
      }
    })

    // remove duplicated shortcut
    return uniqWith(definedShortcuts, (a, b) => a.type === b.type && a.name === b.name)
  }
)

const defaultOpening = createSelector(
  [workspace, tools],
  (workspace, tools) => {
    let defaultTool = null
    if (workspace) {
      if ('resource' === workspace.opening.type) {
        defaultTool = `resources/${workspace.opening.target.slug || ''}`
      } else if ('tool' === workspace.opening.type) {
        defaultTool = workspace.opening.target

        if (!isEmpty(tools)) {
          if (!defaultTool || -1 === tools.findIndex(tool => defaultTool === tool.name)) {
            // no default set or the default tool is not available for the user
            // open the first available tool
            defaultTool = tools[0].name
          }
        }
      }
    }

    return defaultTool
  }
)

// access restrictions selectors
const accessErrors = createSelector(
  [store],
  (store) => !store.accessErrors.dismissed && !isEmpty(store.accessErrors.details) ? store.accessErrors.details : {}
)

export const selectors = {
  STORE_NAME,
  root,
  loaded,
  impersonated,
  notFound,
  workspace,
  managed,
  roles,
  tools,
  shortcuts,
  userShortcuts,
  userEvaluation,
  defaultOpening,
  accessErrors
}
