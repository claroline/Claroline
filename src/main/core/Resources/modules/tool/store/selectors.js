import {createSelector} from 'reselect'

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
  toolData,
  permissions,
  context,
  contextType,
  contextData,
  contextId
}
