import {createSelector} from 'reselect'

const STORE_NAME = 'resource_manager'

const LIST_ROOT_NAME = `${STORE_NAME}.resources`

const store = (state) => state[STORE_NAME]

const root = createSelector(
  [store],
  (store) => store.root
)

export const selectors = {
  STORE_NAME,
  LIST_ROOT_NAME,

  store,
  root
}
