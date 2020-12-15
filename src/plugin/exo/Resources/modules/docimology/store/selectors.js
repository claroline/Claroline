import {createSelector} from 'reselect'

import {selectors as baseSelectors} from '#/plugin/exo/resources/quiz/store/selectors'

const STORE_NAME = 'docimology'

const docimology = createSelector(
  baseSelectors.resource,
  (resource) => resource[STORE_NAME]
)

export const selectors = {
  STORE_NAME,

  docimology
}
