import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as baseSelectors} from '#/main/transfer/tools/transfer/store/selectors'

const STORE_NAME = baseSelectors.STORE_NAME+'.export'

const store = (state) => get(state, STORE_NAME)

const exportFile  = createSelector(
  [store],
  (store) => store.details
)

const exportExplanation = createSelector(
  [store],
  (store) => store.explanation
)

export const selectors = {
  STORE_NAME,

  exportFile,
  exportExplanation
}
