import {connect} from 'react-redux'

import {PlayerMain as PlayerMainComponent} from '#/plugin/path/resources/path/player/components/main'
import {actions, selectors} from '#/plugin/path/resources/path/store'
import {constants} from '#/plugin/path/resources/path/constants'
import {flattenSteps} from '#/plugin/path/resources/path/utils'

const PlayerMain = connect(
  state => ({
    summaryOpened: selectors.summaryOpened(state),
    summaryPinned: selectors.summaryPinned(state),

    path: selectors.path(state),
    navigationEnabled: selectors.navigationEnabled(state),
    fullWidth: selectors.fullWidth(state),
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
    },
    computeResourceDuration(resourceId) {
      dispatch(actions.computeResourceDuration(resourceId))
    }
  })
)(PlayerMainComponent)

export {
  PlayerMain
}
