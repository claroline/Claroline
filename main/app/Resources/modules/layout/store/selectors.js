import {selectors as securitySelectors} from '#/main/app/security/store/selectors'
import {selectors as configSelectors} from '#/main/app/config/store/selectors'

const disabled = (state) => configSelectors.param(state, 'restrictions.disabled')

const maintenance = state => state.maintenance.enabled
const maintenanceMessage = state => state.maintenance.message

const unavailable = (state) => {
  return disabled(state) || (!securitySelectors.isAuthenticated(state) && maintenance(state))
}

const sidebar = state => state.sidebar.name

export const selectors = {
  unavailable,
  disabled,
  maintenance,
  maintenanceMessage,
  sidebar
}
