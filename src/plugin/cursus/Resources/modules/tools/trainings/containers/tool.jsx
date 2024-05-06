import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {reducer, selectors} from '#/plugin/cursus/course/store'

import {TrainingsTool as TrainingsToolComponent} from '#/plugin/cursus/tools/trainings/components/tool'

const TrainingsTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      authenticated: securitySelectors.isAuthenticated(state)
    })
  )(TrainingsToolComponent)
)
export {
  TrainingsTool
}
