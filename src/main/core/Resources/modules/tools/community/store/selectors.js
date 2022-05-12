import {createSelector} from 'reselect'
import get from 'lodash/get'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'
import {constants as toolConstants} from '#/main/core/tool/constants'

const STORE_NAME = 'community'

const store = (state) => state[STORE_NAME]

const restrictions = createSelector(
  [store],
  (store) => store.restrictions
)

const loaded = createSelector(
  [store],
  (store) => store.user.loaded
)

const canCreate = createSelector(
  [toolSelectors.toolData, toolSelectors.contextType, toolSelectors.contextData],
  (tool, contextType, contextData) => {
    const canCreate = hasPermission('create_user', tool)
    if (contextType === toolConstants.TOOL_WORKSPACE) {
      return !get(contextData, 'meta.model') && canCreate
    }

    return canCreate
  }
)

const canCreateRole = createSelector(
  [toolSelectors.toolData],
  (tool) => hasPermission('administrate', tool)
)

const defaultRole = createSelector(
  [toolSelectors.contextType, toolSelectors.contextData],
  (contextType, contextData) => {
    if (contextType === toolConstants.TOOL_WORKSPACE) {
      return contextData.defaultRole
    }

    return null
  }
)

export const selectors = {
  STORE_NAME,

  store,
  loaded,
  restrictions,
  canCreate,
  canCreateRole,
  defaultRole
}
