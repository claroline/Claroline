import {createSelector} from 'reselect'
import isEmpty   from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {isAdmin as userIsAdmin} from '#/main/app/security/permissions'

const STORE_NAME = 'security'

const store = (state) => state[STORE_NAME]

const fakeUser = () => ({
  name: trans('guest'),
  username: trans('guest'),
  roles: [{
    name: 'ROLE_ANONYMOUS',
    translationKey: 'anonymous'
  }]
})

const currentUser = createSelector(
  [store],
  (store) => store.currentUser
)

const isImpersonated = createSelector(
  [store],
  (store) => store.impersonated
)

const isAuthenticated = createSelector(
  [currentUser],
  (currentUser) => !isEmpty(currentUser)
)

const isAdmin = createSelector(
  [currentUser],
  (currentUser) => userIsAdmin(currentUser)
)

export const selectors = {
  STORE_NAME,

  fakeUser,
  currentUser,
  isImpersonated,
  isAuthenticated,
  isAdmin
}
