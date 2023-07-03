import {createSelector} from 'reselect'

const STORE_NAME = 'accountPrivacy'

const store = (state) => state[STORE_NAME]

const privacy = createSelector(
  [store],
  (store) => store.privacy
)
export const selectors = {
  STORE_NAME,
  privacy
}