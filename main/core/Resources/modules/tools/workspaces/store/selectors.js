import {createSelector} from 'reselect'

const STORE_NAME = 'workspaces'

const store = (state) => state[STORE_NAME]

const creatable = createSelector(
  [store],
  (store) => store.creatable
)

export const selectors = {
  STORE_NAME,
  creatable
}
