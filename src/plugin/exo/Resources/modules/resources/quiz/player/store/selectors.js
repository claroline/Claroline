import {createSelector} from 'reselect'

import {selectors as baseSelectors} from '#/plugin/exo/resources/quiz/store/selectors'

const STORE_NAME = 'player'

const player = createSelector(
  [baseSelectors.resource],
  (resourceState) => resourceState[STORE_NAME]
)

const attempt = createSelector(
  [player],
  (playerState) => playerState.attempt
)

export const selectors = {
  STORE_NAME,

  attempt
}
