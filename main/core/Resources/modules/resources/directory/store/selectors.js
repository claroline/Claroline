import {createSelector} from 'reselect'

const STORE_NAME = 'resource'
const EXPLORER_NAME = STORE_NAME+'.directoryExplorer'

const resource = (state) => state[STORE_NAME]

const directory = createSelector(
  [resource],
  (resource) => resource.directory
)

export const selectors = {
  STORE_NAME,
  EXPLORER_NAME,
  directory
}
