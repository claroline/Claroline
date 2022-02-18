import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as baseSelectors} from '#/main/transfer/tools/transfer/store/selectors'

const STORE_NAME = baseSelectors.STORE_NAME+'.import'

const store = (state) => get(state, STORE_NAME)

const importFile  = createSelector(
  [store],
  (store) => store.details
)

const importExplanation = createSelector(
  [store],
  (store) => store.explanation
)

const importSamples = createSelector(
  [store],
  (store) => store.samples
)

export const selectors = {
  STORE_NAME,

  importFile,
  importExplanation,
  importSamples
}
