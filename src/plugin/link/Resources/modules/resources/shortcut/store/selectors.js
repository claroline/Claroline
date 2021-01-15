import {createSelector} from 'reselect'

const STORE_NAME = 'shortcut'

const resource = (state) => state[STORE_NAME]

const embeddedResource = createSelector(
  [resource],
  (resource) => resource.embedded
)

export const selectors = {
  STORE_NAME,
  embeddedResource
}
