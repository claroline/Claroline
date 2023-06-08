import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {VideoPlayer as VideoPlayerComponent} from '#/integration/youtube/resources/video/components/player'
import {actions, selectors} from '#/integration/youtube/resources/video/store'

const VideoPlayer = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    video: selectors.video(state)
  }),
  (dispatch) => ({
    updateProgression(id, currentTime, totalTime) {
      dispatch(actions.updateProgression(id, currentTime, totalTime))
    }
  })
)(VideoPlayerComponent)

export {
  VideoPlayer
}
