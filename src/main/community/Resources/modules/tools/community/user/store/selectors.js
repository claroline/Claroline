import {createSelector} from 'reselect'

import {selectors as baseSelectors} from '#/main/community/tools/community/store/selectors'

const LIST_NAME = baseSelectors.STORE_NAME + '.users.list'
const FORM_NAME = baseSelectors.STORE_NAME + '.users.current'

const users = createSelector(
  [baseSelectors.store],
  (store) => store.users
)

const limitReached = createSelector(
  [users],
  (users) => users.limitReached
)

export const selectors = {
  LIST_NAME,
  FORM_NAME,

  limitReached
}
