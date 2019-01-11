import {createSelector} from 'reselect'

const STORE_NAME = 'resource'

const resource = (state) => state[STORE_NAME]

const ltiResource = createSelector(
  [resource],
  (resource) => resource.ltiResource
)

const ltiApps = createSelector(
  [resource],
  (resource) => resource.ltiApps
)

export const selectors = {
  STORE_NAME,
  ltiResource,
  ltiApps
}