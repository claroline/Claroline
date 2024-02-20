import React, {useRef, useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {Video as VideoTypes} from '#/integration/peertube/prop-types'

const PeerTubePlayer = (props) => {
  const embedIframe = useRef(null)
  const videoInfo = useRef()
  const url = () => {
    const params = {
      api: 1,
      p2p: 0,
      title: 0,
      peertubeLink: props.video.peertubeLink ? 1 : 0,
      autoplay: props.video.autoplay ? 1 : 0,
      muted: props.video.autoplay ? 1 : 0,
      loop: props.video.looping ? 1 : 0,
      controlBar: props.video.controls ? 1 : 0
    }

    if(props.video.timecodeStart && props.video.timecodeStart > 0) {
      params.start = props.video.timecodeStart
    }

    if(props.video.timecodeEnd && props.video.timecodeEnd > props.video.timecodeStart) {
      params.stop = props.video.timecodeEnd
    }

    return props.video.embeddedUrl + '?' + Object.keys(params).map(key => `${key}=${params[key]}`).join('&')
  }

  useEffect(() => {
    return () => {
      if (!isEmpty(videoInfo.current) && props.onPause) {
        props.onPause(videoInfo.current.position, videoInfo.current.duration)
      }
    }
  }, [props.url])

  return (
    <iframe
      style={{minHeight: '540px'}}
      height="100%"
      ref={embedIframe}
      src={url()}
      allowFullScreen={true}
      frameBorder="0"
      onLoad={() => {
        const PeerTubePlayer = window['PeerTubePlayer']
        let player = new PeerTubePlayer(embedIframe.current)

        let updater = (playbackInfo) => {
          let hasChanged = false
          if (isEmpty(videoInfo.current) || videoInfo.current.playbackState !== playbackInfo.playbackState) {
            hasChanged = true
          }

          videoInfo.current = playbackInfo

          if ('playing' === playbackInfo.playbackState) {
            props.onTimeUpdate(playbackInfo.position, playbackInfo.duration)
          }

          if (hasChanged) {
            if ('playing' === playbackInfo.playbackState) {
              if (props.onPlay) {
                props.onPlay(playbackInfo.position, playbackInfo.duration)
              }
            } else if (['paused', 'ended'].includes(playbackInfo.playbackState)) {
              // standard video player do not dispatch an "ended" status (it reuses paused)
              if (props.onPause) {
                props.onPause(playbackInfo.position, playbackInfo.duration)
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
  video: T.shape( VideoTypes.propTypes ).isRequired,
  onPlay: T.func, // get the currentTime and totalDuration as parameters
  onPause: T.func, // get the currentTime and totalDuration as parameters
  onTimeUpdate: T.func // get the currentTime and totalDuration as parameters
}

export {
  PeerTubePlayer
}
