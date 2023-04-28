import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Video as VideoTypes} from '#/integration/peertube/prop-types'
import {PeerTubePlayer} from '#/integration/peertube/components/player'

const VideoPlayer = props => {
  let lastSaved = 0

  return (
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
      onTimeUpdate={(currentTime, duration) => {
        if (props.currentUser) {
          const interval = Math.round((duration / 100) * 5)
          const roundedTime = Math.round(currentTime)
          if (roundedTime > lastSaved && 0 === roundedTime % interval) {
            props.updateProgression(props.video.id, currentTime, duration)
            lastSaved = roundedTime
          }
        }
      }}
    />
  )
}

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
