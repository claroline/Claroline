import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {url} from '#/main/app/api'

import {selectors} from '#/main/core/resources/file/store'
import {Video as VideoTypes} from '#/plugin/video-player/files/video/prop-types'

const Video = props =>
  <video
    className="video-js vjs-big-play-centered vjs-default-skin vjs-16-9 vjs-waiting"
    controls
    data-download={false}
    data-setup={[]}
  >
    <source src={props.file.url} type={props.mimeType} />

    {props.file.tracks && props.file.tracks.map(t =>
      <track
        key={`track-${t.id}`}
        src={url(['api_get_video_track_stream', {track: t.autoId}])}
        label={t.meta.label}
        kind={t.meta.kind}
        srcLang={t.meta.lang}
        default={t.meta.default}
      />
    )}
  </video>

Video.propTypes = {
  mimeType: T.string.isRequired,
  file: T.shape(
    VideoTypes.propTypes
  ).isRequired
}

const VideoPlayer = connect(
  (state) => ({
    mimeType: selectors.mimeType(state)
  })
)(Video)

export {
  VideoPlayer
}
