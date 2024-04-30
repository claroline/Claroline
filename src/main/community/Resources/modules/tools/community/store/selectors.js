import {createSelector} from 'reselect'
import get from 'lodash/get'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'
import {constants as toolConstants} from '#/main/core/tool/constants'

const STORE_NAME = 'community'

const store = (state) => state[STORE_NAME]

const parameters = createSelector(
  [store],
  (store) => store.parameters
)

const profile = createSelector(
  [store],
  (store) => store.profile
)

const registration = createSelector(
  [parameters],
  (parameters) => parameters.registration
)

const canCreate = createSelector(
  [toolSelectors.toolData, toolSelectors.contextType, toolSelectors.contextData],
  (tool, contextType, contextData) => {
    const canCreate = hasPermission('register', tool)
    if (contextType === toolConstants.TOOL_WORKSPACE) {
      return !get(contextData, 'meta.model') && canCreate
    }

    return canCreate
  }
)

const hasPendingRegistrations = createSelector(
  [registration],
  (registration) => get(registration, 'selfRegistration', false) && get(registration, 'validation', false)
)

export const selectors = {
  STORE_NAME,

  store,
  parameters,
  profile,
  registration,
  canCreate,
  hasPendingRegistrations
}
