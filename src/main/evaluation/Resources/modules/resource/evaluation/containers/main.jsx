import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors, reducer} from '#/main/evaluation/resource/evaluation/store'
import {ResourceDashboardEvaluation as ResourceDashboardEvaluationComponent} from '#/main/evaluation/resource/evaluation/components/main'

const ResourceDashboardEvaluation = withReducer(selectors.STORE_NAME, reducer)(
  ResourceDashboardEvaluationComponent
)

export {
  ResourceDashboardEvaluation
}
