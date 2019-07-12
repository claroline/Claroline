import {createSelector} from 'reselect'

const STORE_NAME = 'claroline_web_resource'

const resource = (state) => state[STORE_NAME]

const path = createSelector(
  [resource],
  (resource) => resource.path
)

export const selectors = {
  STORE_NAME,
  resource,
  path
}
