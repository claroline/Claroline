import {createSelector} from 'reselect'

const STORE_NAME = 'claroline_cursus_tool'

const store = (state) => state[STORE_NAME]

const parameters = createSelector(
  [store],
  (store) => store.parameters
)

export const selectors = {
  STORE_NAME,

  store,
  parameters
}