
// check enabled page features
const hasAlerts = (state) => typeof state.alerts !== 'undefined'
const hasModals = (state) => typeof state.modal !== 'undefined'

export const select = {
  hasAlerts,
  hasModals
}
