import {createSelector} from 'reselect'
import isEmpty from 'lodash/isEmpty'

import {isAdmin as userIsAdmin} from '#/main/app/security/permissions'

const STORE_NAME = 'security'

const security = (state) => state[STORE_NAME]

/**
 * Get the user currently authenticated.
 * NB. returns null if no user logged.
 *
 * @return {object|null}
 */
const currentUser = createSelector(
  [security],
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
  [security],
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

export const selectors = {
  STORE_NAME,

  security,
  currentUser,
  currentUserId,
  isImpersonated,
  isAuthenticated,
  isAdmin,
  mainOrganization
}
