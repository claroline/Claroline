
// check enabled page features
const embedded  = (state) => state.embedded
const hasAlerts = (state) => typeof state.alerts !== 'undefined'
const hasModals = (state) => typeof state.modal !== 'undefined'

export const select = {
  embedded,
  hasAlerts,
  hasModals
}
