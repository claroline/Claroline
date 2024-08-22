import {createSelector} from 'reselect'

const STORE_NAME = 'trainingsArchived'
const LIST_NAME = STORE_NAME + '.courses'

const store = (state) => state[STORE_NAME] || {}

const course = createSelector(
  [store],
  (store) => store.course
)

export const selectors = {
  STORE_NAME,
  LIST_NAME,

  course
}
