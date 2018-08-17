import {createSelector} from 'reselect'

const STORE_NAME = 'resource'

const resource = (state) => state[STORE_NAME]

const scorm = createSelector(
  [resource],
  (resource) => resource.scorm
)

const trackings = createSelector(
  [resource],
  (resource) => resource.trackings
)

const scos = createSelector(
  [scorm],
  (scorm) => scorm.scos
)

export const selectors = {
  STORE_NAME,
  scorm,
  scos,
  trackings
}