import {createSelector} from 'reselect'

const STORE_NAME = 'resource'

const resource = (state) => state[STORE_NAME]

const directory = createSelector(
  [resource],
  (resource) => resource.directory
)

export const selectors = {
  STORE_NAME,
  directory
}
