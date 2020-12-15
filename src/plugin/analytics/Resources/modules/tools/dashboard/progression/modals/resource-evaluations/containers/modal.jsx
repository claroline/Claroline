import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {
  actions as listActions
} from '#/main/app/content/list/store'

import {reducer, selectors} from '#/plugin/analytics/tools/dashboard/progression/modals/resource-evaluations/store'
import {ResourceEvaluationsModal as ResourceEvaluationsModalComponent} from '#/plugin/analytics/tools/dashboard/progression/modals/resource-evaluations/components/modal'

const ResourceEvaluationsModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    null,
    (dispatch) => ({
      reset() {
        dispatch(listActions.invalidateData(selectors.STORE_NAME))
      }
    })
  )(ResourceEvaluationsModalComponent)
)

export {
  ResourceEvaluationsModal
}