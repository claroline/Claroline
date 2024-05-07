import {createSelector} from 'reselect'
import get from 'lodash/get'

const STORE_NAME = 'activity'

const store = (state) => get(state, STORE_NAME)

const count = createSelector(
  [store],
  (store) => store.count
)

export const selectors = {
  STORE_NAME,
  
  count
}
