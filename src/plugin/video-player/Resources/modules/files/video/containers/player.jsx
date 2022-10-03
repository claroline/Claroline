import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {VideoPlayer as VideoPlayerComponent} from '#/plugin/video-player/files/video/components/player'
import {actions} from '#/plugin/video-player/files/video/store'

const VideoPlayer = connect(
  (state) => ({
    mimeType: resourceSelectors.mimeType(state),
    currentUser: securitySelectors.currentUser(state)
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
