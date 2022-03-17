import {createSelector} from 'reselect'

import {constants} from '#/main/core/tool/constants'

import {selectors as desktopSelectors} from '#/main/app/layout/sections/desktop/store/selectors'
import {selectors as adminSelectors} from '#/main/app/layout/sections/administration/store/selectors'
import {selectors as workspaceSelectors} from '#/main/core/workspace/store/selectors'

const STORE_NAME = 'tool'

const store = (state) => state[STORE_NAME] || {}
const tool = store

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const notFound = createSelector(
  [store],
  (store) => store.notFound
)

const accessDenied = createSelector(
  [store],
  (store) => store.accessDenied
)

const name = createSelector(
  [store],
  (store) => store.name
)

const basePath = createSelector(
  [store],
  (store) => store.basePath
)

const path = createSelector(
  [basePath, name],
  (basePath, name) => basePath + '/' + name
)

const fullscreen = createSelector(
  [store],
  (store) => store.fullscreen
)

const toolData = createSelector(
  [store],
  (store) => store.data
)

const permissions = createSelector(
  [toolData],
  (toolData) => toolData.permissions
)

const context = createSelector(
  [store],
  (store) => store.currentContext
)

const contextType = createSelector(
  [context],
  (context) => context.type
)

const contextData = createSelector(
  [context],
  (context) => context.data
)

const contextId = createSelector(
  [contextData],
  (contextData) => contextData ? contextData.id : undefined
)

// this should be directly embedded in the contextData to simplify retrieve
// this is not the correct place to do it imo
const contextTools = (state) => {
  const currentContext = contextType(state)
  switch (currentContext) {
    case constants.TOOL_DESKTOP:
      return desktopSelectors.tools(state)
    case constants.TOOL_ADMINISTRATION:
      return adminSelectors.tools(state)
    case constants.TOOL_WORKSPACE:
      return workspaceSelectors.tools(state)
  }

  return []
}

export const selectors = {
  STORE_NAME,
  store,
  tool,

  loaded,
  notFound,
  accessDenied,
  name,
  basePath,
  path,
  fullscreen,
  toolData,
  permissions,
  context,
  contextType,
  contextData,
  contextId,
  contextTools
}
