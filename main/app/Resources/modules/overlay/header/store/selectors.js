import {createSelector} from 'reselect'

import {trans} from '#/main/core/translation'
import {param} from '#/main/app/config'
import {currentUser} from '#/main/core/user/current'

const administration = (state) => state.administration
const current = (state) => state.current
const tools = (state) => state.tools
const userTools = (state) => state.userTools
const workspaces = (state) => state.workspaces
const count = (state) => state.notifications.count

const personalWorkspace = createSelector(
  [workspaces],
  (workspaces) => workspaces.personal
)

const currentWorkspace = createSelector(
  [workspaces],
  (workspaces) => workspaces.current
)

const workspacesHistory = createSelector(
  [workspaces],
  (workspaces) => workspaces.history
)

const display = (state) => state.display

// this will later be retrieved from the store
const logo = () => param('logo')
const redirectHome = () => ('logo_redirect_home')
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
  current,
  tools,
  userTools,
  workspaces,
  count,
  personalWorkspace,
  currentWorkspace,
  workspacesHistory,
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
