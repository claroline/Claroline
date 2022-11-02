import {createSelector} from 'reselect'

import {selectors as baseSelectors} from '#/main/community/administration/community/store'

const users = createSelector(
  [baseSelectors.store],
  (store) => store.users
)

const limitReached = createSelector(
  [users],
  (users) => users.limitReached
)

export const selectors = {
  limitReached
}
