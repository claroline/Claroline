import {createSelector} from 'reselect'

import {constants} from '#/main/app/overlay/alert/constants'

const STORE_NAME = 'alerts'

const alerts = state => state[STORE_NAME]

const sortedAlerts = createSelector(
  [alerts],
  (alerts) => alerts.slice(0).sort((alertA, alertB) => {
    const orderA = constants.ALERT_STATUS[alertA.status].order
    const orderB = constants.ALERT_STATUS[alertB.status].order

    if (orderA < orderB) {
      return -1
    } else if (orderA > orderB) {
      return 1
    }
    return 0
  })
)

const displayedAlerts = createSelector(
  [sortedAlerts],
  (sortedAlerts) => sortedAlerts.slice(0, constants.ALERT_DISPLAY_MAX)
)

export const selectors = {
  STORE_NAME,
  alerts,
  sortedAlerts,
  displayedAlerts
}
