import {createSelector} from 'reselect'

import {selectors as baseSelectors} from '#/plugin/exo/resources/quiz/store/selectors'

const STORE_NAME = 'player'

const player = createSelector(
  [baseSelectors.resource],
  (resourceState) => resourceState[STORE_NAME]
)

const paperCount = createSelector(
  [player],
  (playerState) => playerState.paperCount
)

const userPaperCount = createSelector(
  [player],
  (playerState) => playerState.userPaperCount
)

const userPaperDayCount = createSelector(
  [player],
  (playerState) => playerState.userPaperDayCount
)

const attempt = createSelector(
  [player],
  (playerState) => playerState.attempt
)

export const selectors = {
  STORE_NAME,

  paperCount,
  userPaperCount,
  userPaperDayCount,
  attempt
}
