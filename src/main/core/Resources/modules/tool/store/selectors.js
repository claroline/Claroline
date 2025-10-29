import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as contextSelectors} from '#/main/app/context/store/selectors'
import {hasPermission as permissionChecker} from '#/main/app/security'

const STORE_NAME = 'tool'
const EDITOR_NAME = 'toolEditor'

const store = (state) => state[STORE_NAME]

const loaded = createSelector(
  [store],
  (store) => get(store, 'loaded', false)
)

const name = createSelector(
  [store],
  (store) => get(store, 'name')
)

/**
 * @deprecated
 */
const basePath = contextSelectors.path

const path = createSelector(
  [contextSelectors.path, name],
  (basePath, name) => basePath + '/' + name
)

const tool = createSelector(
  [contextSelectors.tools, name],
  (tools, name) => tools.find(tool => tool.name === name) || {}
)

const poster = createSelector(
  [tool],
  (tool) => tool.poster
)

const permissions = createSelector(
  [tool],
  (tool) => tool.permissions
)

const contextPath = contextSelectors.path
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

const hasPermission = (permission, state) => {
  const data = tool(state)

  return permissionChecker(permission, data)
}

/**
 * @deprecated use one of contextType, contextData.
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
  EDITOR_NAME,
  tool,

  loaded,
  name,
  basePath,
  path,
  poster,
  permissions,
  hasPermission,
  context,
  contextPath,
  contextType,
  contextData,
  contextId,
  contextTools
}
