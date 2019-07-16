import {createSelector} from 'reselect'

const STORE_NAME = 'ujm_lti_resource'

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