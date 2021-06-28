import {createSelector} from 'reselect'

const STORE_NAME = 'documentation'
const LIST_NAME = STORE_NAME+'.list'

const store = (state) => state[STORE_NAME]

const current = createSelector(
  [store],
  (store) => store.current
)

export const selectors = {
  STORE_NAME,
  LIST_NAME,

  current
}
