import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as baseSelectors} from '#/main/community/tools/community/store/selectors'

const STORE_NAME = baseSelectors.STORE_NAME + '.activity'

const store = (state) => get(state, STORE_NAME)

const count = createSelector(
  [store],
  (store) => store.count
)

const actionTypes = createSelector(
  [store],
  (store) => store.actionTypes
)

export const selectors = {
  STORE_NAME,
  
  count,
  actionTypes
}
