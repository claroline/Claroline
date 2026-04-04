import {createSelector} from 'reselect'

const STORE_NAME = 'claroline_project'

const store = (state) => state[STORE_NAME] || {}

const project = createSelector(
  [store],
  (store) => store.resource
)

export const selectors = {
  STORE_NAME,
  project
}
