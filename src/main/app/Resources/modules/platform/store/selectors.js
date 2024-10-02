import {createSelector} from 'reselect'
import isEmpty from 'lodash/isEmpty'

import {now} from '#/main/app/intl/date'

import {selectors as configSelectors} from '#/main/app/config/store/selectors'

const restrictions = (state) => configSelectors.param(state, 'restrictions') || {}

const restrictionDisabled = createSelector(
  [restrictions],
  (restrictions) => restrictions.disabled || false
)

const restrictionDates = createSelector(
  [restrictions],
  (restrictions) => restrictions.dates || []
)

const disabled = createSelector(
  [restrictionDates, restrictionDisabled],
  (restrictionDates, restrictionDisabled) => {
    const started = !restrictionDates[0] || restrictionDates[0] < now(false)
    const ended   = restrictionDates[1] && restrictionDates[1] < now(false)

    return restrictionDisabled || !started || ended
  }
)

const unavailable = (state) => disabled(state)

const selfRegistration = (state) => configSelectors.param(state, 'selfRegistration')

const availableContexts = (state) => state.contexts

const favoriteContexts = (state) => state.contextFavorites

const isContextFavorite = (state, context) => {
  if (isEmpty(state.contextFavorites)) {
    return false
  }

  return -1 !== state.contextFavorites.findIndex(f => f.id === context.id)
}

const currentOrganization = (state) => state.currentOrganization
const availableOrganizations = (state) => state.availableOrganizations

export const selectors = {
  unavailable,
  disabled,
  selfRegistration,
  availableContexts,
  favoriteContexts,
  isContextFavorite,
  currentOrganization,
  availableOrganizations
}
