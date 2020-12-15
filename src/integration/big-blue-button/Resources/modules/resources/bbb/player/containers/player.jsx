import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {actions, selectors} from '#/integration/big-blue-button/resources/bbb/store'
import {Player as PlayerComponent} from '#/integration/big-blue-button/resources/bbb/player/components/player'

const Player = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    currentUser: securitySelectors.currentUser(state),
    bbb: selectors.bbb(state),
    lastRecording: selectors.lastRecording(state),
    canStart: selectors.canStart(state),
    joinStatus: selectors.joinStatus(state),
    allowRecords: selectors.allowRecords(state)
  }),
  (dispatch) => ({
    createMeeting(bbb) {
      return dispatch(actions.createMeeting(bbb))
    }
  })
)(PlayerComponent)

export {
  Player
}
