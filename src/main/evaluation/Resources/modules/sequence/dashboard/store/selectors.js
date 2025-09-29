import get from 'lodash/get'
import {createSelector} from 'reselect'

import {trans} from '#/main/app/intl/translation'
import {selectors as sequenceSelectors} from '#/main/evaluation/sequence/store/selectors'

const STORE_NAME = 'sequenceDashboard'

const path = createSelector(
  [sequenceSelectors.path],
  (sequencePath) => sequencePath + '/dashboard'
)

const activities = createSelector(
  [sequenceSelectors.orderedSteps],
  (steps) => {
    const types = {}

    steps.map(step => {
      if (step.primaryResource) {
        const resourceType = trans(get(step.primaryResource, 'meta.type'), {}, 'resource')
        if (types[resourceType]) {
          types[resourceType] += 1
        } else {
          types[resourceType] = 1
        }
      }
    })

    return Object.keys(types).map(type => ({
      label: type,
      value: types[type]
    }))
  }
)

export const selectors = {
  STORE_NAME,
  path,
  activities
}
