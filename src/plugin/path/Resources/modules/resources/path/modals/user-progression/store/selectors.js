import {createSelector} from 'reselect'

const STORE_NAME = 'userStepsProgression'

const store = (state) => state[STORE_NAME]

const stepsProgression = createSelector(
  [store],
  (store) => store.progression
)

const lastAttempt = createSelector(
  [store],
  (store) => store.lastAttempt
)

const resourceEvaluations = createSelector(
  [store],
  (store) => store.resourceEvaluations
)

export const selectors = {
  STORE_NAME,

  stepsProgression,
  lastAttempt,
  resourceEvaluations
}
