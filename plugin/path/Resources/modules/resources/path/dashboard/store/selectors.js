import {createSelector} from 'reselect'

import {selectors as pathSelectors} from '#/plugin/path/resources/path/store/selectors'

const STORE_NAME = pathSelectors.STORE_NAME+'.dashboard'

const dashboard = createSelector(
  [pathSelectors.resource],
  (resource) => resource.dashboard
)

const stepsProgression = createSelector(
  [dashboard],
  (dashboard) => dashboard.userStepsProgression
)

export const selectors = {
  STORE_NAME,
  dashboard,
  stepsProgression
}
