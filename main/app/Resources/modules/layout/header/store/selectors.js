import {createSelector} from 'reselect'

import {param} from '#/main/app/config'

const STORE_NAME = 'header'

const store = (state) => state[STORE_NAME]

const menus = createSelector(
  [store],
  (store) => store.menus
)
const administration = createSelector(
  [store],
  (store) => store.administration
)
const tools = createSelector(
  [store],
  (store) => store.tools
)
const notificationTools = createSelector(
  [store],
  (store) => store.notificationTools
)
const count = createSelector(
  [store],
  (store) => store.notifications.count
)

const display = createSelector(
  [store],
  (store) => store.display
)

// this will later be retrieved from the store
const logo = () => param('logo')
const redirectHome = () => param('logo_redirect_home') || false
const title = () => param('name')
const subtitle = () => param('secondaryName')

const locale = () => param('locale')

const loginUrl = () => param('links.login')

const helpUrl = createSelector(
  [display],
  (display) => {
    if (display.help) {
      return param('links.help')
    }

    return null
  }
)

const registrationUrl = createSelector(
  [display],
  (display) => {
    if (display.registration) {
      return param('links.registration')
    }

    return null
  }
)

const maintenance = () => param('maintenance')

export const selectors = {
  STORE_NAME,

  menus,
  administration,
  tools,
  notificationTools,
  count,
  logo,
  redirectHome,
  title,
  subtitle,
  display,
  locale,
  loginUrl,
  helpUrl,
  registrationUrl,
  maintenance
}
