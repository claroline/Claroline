import {createSelector} from 'reselect'

import {selectors as contextSelectors} from '#/main/app/context/store/selectors'

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

/**
 * @deprecated
 */
const basePath = contextSelectors.path

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
  (store) => store.data || {}
)

const permissions = createSelector(
  [toolData],
  (toolData) => toolData.permissions
)

const contextType = contextSelectors.type

const contextData = createSelector(
  [contextSelectors.data],
  // FIXME : for retro compatibility, tools expect empty data for every context except workspace
  (contextData) => contextData && contextData.id ? contextData : null
)

const contextId = createSelector(
  [contextData],
  (contextData) => contextData ? contextData.id : undefined
)

/**
 * @deprecated use one of contextType, contextData, contextType.
 */
const context = createSelector(
  [contextType, contextData],
  (contextType, contextData) => ({
    type: contextType,
    data: contextData
  })
)

// this should be directly embedded in the contextData to simplify retrieve
// this is not the correct place to do it imo
const contextTools = contextSelectors.tools

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
