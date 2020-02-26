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
 * Checks if the current is a platform admin (aka has the ROLE_ADMIN role).
 *
 * @return {bool}
 */
const isAdmin = createSelector(
  [currentUser],
  (currentUser) => userIsAdmin(currentUser)
)

export const selectors = {
  STORE_NAME,

  fakeUser,
  currentUser,
  currentUserId,
  isImpersonated,
  isAuthenticated,
  isAdmin
}
