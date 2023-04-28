import React, {useRef} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

const PeerTubePlayer = (props) => {
  const embedIframe = useRef(null)

  return (
    <iframe
      style={{minHeight: '540px'}}
      height="100%"
      ref={embedIframe}
      src={`${props.url}?api=1&p2p=0&title=0&peertubeLink=0`}
      allowFullScreen={true}
      frameBorder="0"
      onLoad={() => {
        const PeerTubePlayer = window['PeerTubePlayer']
        let player = new PeerTubePlayer(embedIframe.current)

        let currentInfo
        let updater = (playbackInfo) => {
          if ('playing' === playbackInfo.playbackState) {
            props.onTimeUpdate(playbackInfo.position, playbackInfo.duration)
          }

          if (isEmpty(currentInfo) || currentInfo.playbackState !== playbackInfo.playbackState) {
            currentInfo = playbackInfo

            if ('playing' === playbackInfo.playbackState) {
              if (props.onPlay) {
                props.onPlay(currentInfo.position, currentInfo.duration)
              }
            } else if (['paused', 'ended'].includes(playbackInfo.playbackState)) {
              // standard video player do not dispatch a "ended" status (it reuses paused)
              if (props.onPause) {
                props.onPause(currentInfo.position, currentInfo.duration)
              }

              player.removeEventListener('playbackStatusUpdate', updater)
            }
          }
        }

        player.ready.then(() => {
          player.addEventListener('playbackStatusChange', (status) => {
            if ('playing' === status) {
              player.addEventListener('playbackStatusUpdate', updater)
            }
          })
        })
      }}
    />
  )
}

PeerTubePlayer.propTypes = {
  url: T.string.isRequired,
  onPlay: T.func, // get the currentTime and totalDuration as parameters
  onPause: T.func, // get the currentTime and totalDuration as parameters
  onTimeUpdate: T.func // get the currentTime and totalDuration as parameters
}

export {
  PeerTubePlayer
}
