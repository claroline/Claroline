import {createSelector} from 'reselect'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {isAdmin as userIsAdmin} from '#/main/app/security/permissions'

const STORE_NAME = 'security'

const store = (state) => state[STORE_NAME]

/**
 * Get a user placeholder object.
 *
 * @return {object}
 */
const fakeUser = () => ({
  name: trans('guest'),
  username: trans('guest'),
  roles: [{
    name: 'ROLE_ANONYMOUS',
    translationKey: 'anonymous'
  }]
})

/**
 * Get the user currently authenticated.
 * NB. returns null if no user logged.
 *
 * @return {object|null}
 */
const currentUser = createSelector(
  [store],
  (store) => store.currentUser
)

const currentUserId = createSelector(
  [currentUser],
  (currentUser) => currentUser ? currentUser.id : null
)

/**
 * Checks if the current user usurp another user account.
 *
 * @return {bool}
 */
const isImpersonated = createSelector(
  [store],
  (store) => store.impersonated
)

/**
 * Checks if there is a user authenticated.
 *
 * @return {bool}
 */
const isAuthenticated = createSelector(
  [currentUser],
  (currentUser) => !isEmpty(currentUser)
)

/**
 * Checks if the current user is a platform admin (aka has the ROLE_ADMIN role).
 *
 * @return {bool}
 */
const isAdmin = createSelector(
  [currentUser],
  (currentUser) => userIsAdmin(currentUser)
)

/**
 * Get the main organization of the current user.
 * Useful to set the default in forms when creating new objects which are linked to organizations.
 *
 * NB. returns null if no main organization (should not be possible).
 *
 * @return {object|null}
 */
const mainOrganization = createSelector(
  [currentUser],
  (currentUser) => currentUser.mainOrganization
)

/**
 * Checks if the current user has access to administration section.
 *
 * @return {bool}
 */
const hasAdministration = createSelector(
  [store],
  (store) => store.administration
)

const clientIp = createSelector(
  [store],
  (store) => {
    let ip = store.client.ip
    if (store.client.forwarded) {
      ip += ' / ' + store.client.forwarded
    }

    return ip
  }
)

export const selectors = {
  STORE_NAME,

  fakeUser,
  currentUser,
  currentUserId,
  isImpersonated,
  isAuthenticated,
  isAdmin,
  mainOrganization,
  hasAdministration,
  clientIp
}
