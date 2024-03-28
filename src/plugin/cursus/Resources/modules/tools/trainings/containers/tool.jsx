import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {TrainingsTool as TrainingsToolComponent} from '#/plugin/cursus/tools/trainings/components/tool'
import {reducer, selectors} from '#/plugin/cursus/tools/trainings/store'
import {withReducer} from '#/main/app/store/reducer'

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
