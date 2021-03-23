import {createSelector} from 'reselect'

const STORE_NAME = 'taskDetails'

const store = (state) => state[STORE_NAME] || {}

const loaded = createSelector(
  [store],
  (store) => store.loaded
)

const task = createSelector(
  [store],
  (store) => store.task
)

export const selectors = {
  STORE_NAME,
  task,
  loaded
}
