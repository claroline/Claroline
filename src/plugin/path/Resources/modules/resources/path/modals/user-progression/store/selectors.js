import {createSelector} from 'reselect'

const STORE_NAME = 'userStepsProgression'

const store = (state) => state[STORE_NAME]

const stepsProgression = createSelector(
  [store],
  (store) => store.userStepsProgression
)

export const selectors = {
  STORE_NAME,

  stepsProgression
}
