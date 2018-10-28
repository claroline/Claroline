import {createSelector} from 'reselect'

import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {currentUser} from '#/main/core/user/current'

const administration = (state) => state.administration
const context = (state) => state.context
const tools = (state) => state.tools
const userTools = (state) => state.userTools
const notificationTools = (state) => state.notificationTools
const count = (state) => state.notifications.count

const display = (state) => state.display

// this will later be retrieved from the store
const logo = () => param('logo')
const redirectHome = () => param('logo_redirect_home') || false
const title = () => param('name')
const subtitle = () => param('secondaryName')
const user = () => {
  let current = currentUser()
  if (!current) {
    // create a fake user for anonymous
    // this is a little hacky and cannot be used has a real user
    current = {
      name: trans('guest'),
      username: trans('guest'),
      roles: [{
        name: 'ROLE_ANONYMOUS',
        translationKey: 'anonymous'
      }]
    }
  }

  return current
}
const authenticated = () => !!currentUser()

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
  administration,
  context,
  tools,
  userTools,
  notificationTools,
  count,
  logo,
  redirectHome,
  title,
  subtitle,
  display,
  user,
  authenticated,
  locale,
  loginUrl,
  helpUrl,
  registrationUrl,
  maintenance
}
