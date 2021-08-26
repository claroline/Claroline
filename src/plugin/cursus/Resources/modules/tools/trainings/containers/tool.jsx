import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {TrainingsTool as TrainingsToolComponent} from '#/plugin/cursus/tools/trainings/components/tool'

const TrainingsTool = connect(
  (state) => ({
    authenticated: securitySelectors.isAuthenticated(state)
  })
)(TrainingsToolComponent)

export {
  TrainingsTool
}
