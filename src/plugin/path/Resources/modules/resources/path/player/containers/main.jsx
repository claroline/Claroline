import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {PlayerMain as PlayerMainComponent} from '#/plugin/path/resources/path/player/components/main'
import {actions, selectors} from '#/plugin/path/resources/path/store'
import {constants} from '#/plugin/path/resources/path/constants'
import {flattenSteps} from '#/plugin/path/resources/path/utils'

const PlayerMain = withRouter(connect(
  state => ({
    basePath: resourceSelectors.path(state),
    currentUser: securitySelectors.currentUser(state),
    resourceId: resourceSelectors.id(state),
    path: selectors.path(state),
    navigationEnabled: selectors.navigationEnabled(state),
    steps: flattenSteps(selectors.steps(state)),
    workspace: resourceSelectors.workspace(state),
    attempt: selectors.attempt(state),
    stepsProgression: selectors.stepsProgression(state),
    resourceEvaluations: selectors.resourceEvaluations(state)
  }),
  dispatch => ({
    updateProgression(stepId, status = constants.STATUS_SEEN, silent) {
      dispatch(actions.updateProgression(stepId, status, silent))
    },
    getAttempt(pathId) {
      return dispatch(actions.getAttempt(pathId))
    },
    enableNavigation() {
      dispatch(actions.enableNavigation())
    },
    disableNavigation() {
      dispatch(actions.disableNavigation())
    }
  })
)(PlayerMainComponent))

export {
  PlayerMain
}
