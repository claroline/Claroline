import {now} from '#/main/app/intl/date'

import {selectors as securitySelectors} from '#/main/app/security/store/selectors'
import {selectors as configSelectors} from '#/main/app/config/store/selectors'

const disabled = (state) => {
  const started = !configSelectors.param(state, 'restrictions.dates[0]') || configSelectors.param(state, 'restrictions.dates[0]') < now(false)
  const ended   = configSelectors.param(state, 'restrictions.dates[1]') && configSelectors.param(state, 'restrictions.dates[1]') < now(false)

  return configSelectors.param(state, 'restrictions.disabled')
    || !started
    || ended
}

const maintenance = state => state.maintenance.enabled
const maintenanceMessage = state => state.maintenance.message

const unavailable = (state) => disabled(state) || (!securitySelectors.isAuthenticated(state) && maintenance(state))

export const selectors = {
  unavailable,
  disabled,
  maintenance,
  maintenanceMessage
}
