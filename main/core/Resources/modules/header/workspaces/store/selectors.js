import {createSelector} from 'reselect'

/*import {selectors as headerSelectors from '#/main/core'}*/

const STORE_NAME = 'workspacesMenu'

const store = (state) => state[STORE_NAME]

const personal = createSelector(
  [store],
  (store) => store.personal
)

const history = createSelector(
  [store],
  (store) => store.history
)

const creatable = createSelector(
  [store],
  (store) => store.creatable
)

export const selectors = {
  STORE_NAME,
  personal,
  history,
  creatable
}
