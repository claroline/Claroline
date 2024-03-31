import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'

import {hasPermission} from '#/main/app/security'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {reducer as courseReducer, selectors as courseSelectors} from '#/plugin/cursus/course/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {TrainingsTool as TrainingsToolComponent} from '#/plugin/cursus/tools/trainings/components/tool'
import {reducer, selectors} from '#/plugin/cursus/tools/trainings/store'

const TrainingsTool = withReducer(selectors.STORE_NAME, reducer)(
  withReducer(courseSelectors.STORE_NAME, courseReducer)(
    connect(
      (state) => ({
        authenticated: securitySelectors.isAuthenticated(state),
        canEdit: hasPermission('edit', toolSelectors.toolData(state)),
        canRegister: hasPermission('register', toolSelectors.toolData(state))
      })
    )(TrainingsToolComponent)
  )
)

export {
  TrainingsTool
}
