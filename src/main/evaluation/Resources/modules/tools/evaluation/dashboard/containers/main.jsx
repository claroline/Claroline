import {withReducer} from '#/main/app/store/reducer'

import {EvaluationDashboard as EvaluationDashboardComponent} from '#/main/evaluation/tools/evaluation/dashboard/components/main'
import {reducer, selectors} from '#/main/evaluation/tools/evaluation/dashboard/store'

const EvaluationDashboard = withReducer(selectors.STORE_NAME, reducer)(
  EvaluationDashboardComponent
)

export {
  EvaluationDashboard
}
