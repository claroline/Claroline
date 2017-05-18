import {makeReducer} from '#/main/core/utilities/redux'

import {
  STATISTICS_SET
} from './../actions/statistics'

function setStatistics(statisticsState, action = {}) {
  return action.statistics
}

const statisticsReducer = makeReducer({}, {
  [STATISTICS_SET]: setStatistics
})

export default statisticsReducer
