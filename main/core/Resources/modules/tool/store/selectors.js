import {createSelector} from 'reselect'

const STORE_NAME = 'tool'

const store = (state) => state[STORE_NAME] || {}

const loaded = createSelector(
  [store],
  (store) => store.loaded
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

const icon = createSelector(
  [store],
  (store) => store.icon
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
  (contextData) => contextData ? contextData.uuid : undefined
)

export const selectors = {
  STORE_NAME,
  store,

  loaded,
  name,
  basePath,
  path,
  icon,
  context,
  contextType,
  contextData,
  contextId
}
