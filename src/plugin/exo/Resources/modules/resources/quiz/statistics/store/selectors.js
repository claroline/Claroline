import {createSelector} from 'reselect'

import {selectors as baseSelectors} from '#/plugin/exo/resources/quiz/store/selectors'

const STORE_NAME = 'statistics'

const statStore = createSelector(
  baseSelectors.store,
  (resource) => resource[STORE_NAME]
)

const statistics = createSelector(
  statStore,
  (statStore) => statStore.answers
)

const docimology = createSelector(
  statStore,
  (statStore) => statStore.docimology
)

export const selectors = {
  STORE_NAME,

  statistics,
  docimology
}
