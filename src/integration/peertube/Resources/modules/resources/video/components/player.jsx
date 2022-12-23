import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Video as VideoTypes} from '#/integration/peertube/prop-types'
import {PeerTubePlayer} from '#/integration/peertube/components/player'

const VideoPlayer = props =>
  <PeerTubePlayer
    url={props.video.embeddedUrl}
    onPlay={(currentTime, duration) => {
      if (props.currentUser) {
        props.updateProgression(props.video.id, currentTime, duration)
      }
    }}
    onPause={(currentTime, duration) => {
      if (props.currentUser) {
        props.updateProgression(props.video.id, currentTime, duration)
      }
    }}
  />

VideoPlayer.propTypes = {
  video: T.shape(
    VideoTypes.propTypes
  ).isRequired,
  updateProgression: T.func.isRequired,
  currentUser: T.object
}

export {
  VideoPlayer
}
