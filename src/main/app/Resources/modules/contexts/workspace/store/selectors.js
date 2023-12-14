import {createSelector} from 'reselect'

const STORE_NAME = 'workspace'

const store = (state) => state[STORE_NAME] || {}

const root = createSelector(
  [store],
  (store) => store.root
)

const userEvaluation = createSelector(
  [store],
  (store) => store.userEvaluation
)

export const selectors = {
  STORE_NAME,

  root,
  userEvaluation
}
