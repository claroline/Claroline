import {createSelector} from 'reselect'

import {now} from '#/main/app/intl/date'

import {selectors as securitySelectors} from '#/main/app/security/store/selectors'
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

const maintenance = state => state.maintenance.enabled
const maintenanceMessage = state => state.maintenance.message

const unavailable = (state) => disabled(state) || (!securitySelectors.isAuthenticated(state) && maintenance(state))

const selfRegistration = (state) => configSelectors.param(state, 'selfRegistration')

const availableContexts = (state) => state.contexts

export const selectors = {
  unavailable,
  disabled,
  maintenance,
  maintenanceMessage,
  selfRegistration,
  availableContexts
}
