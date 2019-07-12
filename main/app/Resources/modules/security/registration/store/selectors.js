import {createSelector} from 'reselect'

const STORE_NAME = 'registration'
const FORM_NAME = `${STORE_NAME}.form`

const store = (state) => state[STORE_NAME]

const termOfService = createSelector(
  [store],
  (store) => store.termOfService
)

const options = createSelector(
  [store],
  (store) => store.options
)

const facets = createSelector(
  [store],
  (store) => store.facets
)

const workspaces = createSelector(
  [store],
  (store) => store.workspaces
)

const defaultWorkspaces = createSelector(
  [store],
  (store) => store.defaultWorkspaces
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,

  termOfService,
  options,
  facets,
  workspaces,
  defaultWorkspaces
}
