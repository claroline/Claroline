import {createSelector} from 'reselect'

const scorm = state => state.scorm
const trackings = state => state.trackings

const scos = createSelector(
  [scorm],
  (scorm) => scorm.scos
)

export const select = {
  scorm,
  scos,
  trackings
}