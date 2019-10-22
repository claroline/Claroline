import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {PlayerMain as PlayerMainComponent} from '#/plugin/path/resources/path/player/components/main'
import {actions, selectors} from '#/plugin/path/resources/path/store'
import {constants} from '#/plugin/path/resources/path/constants'
import {flattenSteps} from '#/plugin/path/resources/path/utils'

const PlayerMain = connect(
  state => ({
    basePath: resourceSelectors.path(state),
    currentUser: securitySelectors.currentUser(state),
    path: selectors.path(state),
    navigationEnabled: selectors.navigationEnabled(state),
    steps: flattenSteps(selectors.steps(state))
  }),
  dispatch => ({
    updateProgression(stepId, status = constants.STATUS_SEEN, silent) {
      dispatch(actions.updateProgression(stepId, status, silent))
    },
    enableNavigation() {
      dispatch(actions.enableNavigation())
    },
    disableNavigation() {
      dispatch(actions.disableNavigation())
    }
  })
)(PlayerMainComponent)

export {
  PlayerMain
}
