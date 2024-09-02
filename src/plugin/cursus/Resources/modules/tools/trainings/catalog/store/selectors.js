import {createSelector} from 'reselect'

const STORE_NAME = 'trainingCatalog'
const LIST_NAME = STORE_NAME + '.courses'

const catalog = (state) => state[STORE_NAME] || {}

const course = createSelector(
  [catalog],
  (catalog) => catalog.course
)


export const selectors = {
  STORE_NAME,
  LIST_NAME,

  course
}
