import React, {useEffect, useRef} from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

/* global videojs */

const Video = (props) => {
  const videoRef = useRef(null)
  const playerRef = useRef(null)
  const {options, onReady, sources} = props

  useEffect(() => {
    // Make sure Video.js player is only initialized once
    if (!playerRef.current) {
      // The Video.js player needs to be _inside_ the component el for React 18 Strict Mode.
      const videoElement = document.createElement('video-js')

      videoElement.classList.add('vjs-big-play-centered')
      videoRef.current.appendChild(videoElement)

      const player = playerRef.current = videojs(videoElement, merge({}, options, {sources: sources}), () => {
        onReady && onReady(player)
      })

      // You could update an existing player in the `else` block here
      // on prop change, for example:
    } else {
      const player = playerRef.current

      player.controls(options.controls)
      player.autoplay(options.autoplay)
      player.src(sources)
    }
  }, [options, videoRef])

  // Dispose the Video.js player when the functional component unmounts
  useEffect(() => {
    const player = playerRef.current

    return () => {
      if (player && !player.isDisposed()) {
        player.dispose()
        playerRef.current = null
      }
    }
  }, [playerRef])

  return (
    <div data-vjs-player className={props.className} ref={videoRef} />
  )
}

Video.propTypes = {
  className: T.string,
  options: T.object,
  sources: T.arrayOf(T.shape({
    src: T.string.isRequired,
    type: T.string
  })),
  onReady: T.func
}

export {
  Video
}
