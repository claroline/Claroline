import React from 'react'
import {PropTypes as T} from 'prop-types'

import {File as FileTypes} from '#/main/core/files/prop-types'

// The wrapping div is required because it will throw a JS on unmount as the parent is a Fragment.
// Search for "Uncaught DOMException: Node.removeChild: The node to be removed is not a child of this node" for mor information.
const VideoPlayer = props => {
  let lastSaved = 0

  return (
    <div className="video-container">
      <video
        height="auto"
        className="video-js vjs-big-play-centered vjs-default-skin vjs-16-9 vjs-waiting"
        controls={true}
        data-download={false}
        onPlay={(e) => {
          if (props.currentUser) {
            props.updateProgression(props.file.id, e.target.currentTime, e.target.duration)
          }
        }}
        onPause={(e) => {
          if (props.currentUser) {
            props.updateProgression(props.file.id, e.target.currentTime, e.target.duration)
          }
        }}
        onTimeUpdate={(e) => {
          if (props.currentUser) {
            const interval = Math.round((e.target.duration / 100) * 5)
            const currentTime = Math.round(e.target.currentTime)
            if (currentTime > lastSaved && 0 === currentTime % interval) {
              props.updateProgression(props.file.id, e.target.currentTime, e.target.duration)
              lastSaved = currentTime
            }
          }
        }}
      >
        <source src={props.file.url} type={props.mimeType} />
      </video>
    </div>
  )
}



VideoPlayer.propTypes = {
  mimeType: T.string.isRequired,
  file: T.shape(
    FileTypes.propTypes
  ).isRequired,
  updateProgression: T.func.isRequired,
  currentUser: T.object
}

export {
  VideoPlayer
}
