import {createSelector} from 'reselect'

const STORE_NAME = 'accountPrivacy'
const store = (state) => state[STORE_NAME]

const dpo = createSelector(
  [store],
  (store) => store.dpo
)
export const selectors = {
  STORE_NAME,
  store,
  dpo
}
