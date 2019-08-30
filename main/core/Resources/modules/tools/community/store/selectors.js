import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as securitySelectors} from '#/main/app/security/store/selectors'
import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'
import {constants as toolConstants} from '#/main/core/tool/constants'

import {getPermissionLevel} from '#/main/core/tools/community/permissions'
import {constants} from '#/main/core/tools/community/constants'

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
  [securitySelectors.currentUser, toolSelectors.contextType, toolSelectors.contextData],
  (currentUser, contextType, contextData) => {
    if (contextType === toolConstants.TOOL_WORKSPACE) {
      const permLevel = getPermissionLevel(currentUser, contextData)

      return !get(contextData, 'meta.model') && permLevel === constants.ADMIN
    }

    return false
  }
)

const canRegister = createSelector(
  [securitySelectors.currentUser, toolSelectors.contextType, toolSelectors.contextData],
  (currentUser, contextType, contextData) => {
    if (contextType === toolConstants.TOOL_WORKSPACE) {
      const permLevel = getPermissionLevel(currentUser, contextData)

      return !get(contextData, 'meta.model') && (permLevel === constants.MANAGER || permLevel === constants.ADMIN)
    }

    return false
  }
)

const defaultRole = createSelector(
  [toolSelectors.contextType, toolSelectors.contextData],
  (contextType, contextData) => {
    if (contextType === toolConstants.TOOL_WORKSPACE) {
      return contextData.roles.find(role => role.translationKey === 'collaborator')
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
  canRegister,
  defaultRole
}
