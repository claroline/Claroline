import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors, reducer} from '#/main/evaluation/analytics/resource/evaluation/store'
import {EvaluationDashboard as EvaluationDashboardComponent} from '#/main/evaluation/analytics/resource/evaluation/components/dashboard'

const EvaluationDashboard = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      nodeId: resourceSelectors.id(state)
    })
  )(EvaluationDashboardComponent)
)

export {
  EvaluationDashboard
}
