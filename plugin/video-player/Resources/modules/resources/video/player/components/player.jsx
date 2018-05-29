import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {url} from '#/main/app/api'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'

import {Track as TrackTypes} from '#/plugin/video-player/resources/video/prop-types'

const PlayerComponent = props =>
  <video
    className="video-js vjs-big-play-centered vjs-default-skin vjs-16-9 vjs-waiting"
    controls
    data-download={props.canDownload}
    data-setup={[]}
  >
    <source src={props.url} type={props.resource.meta.mimeType} />
    {props.tracks.map(t =>
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

PlayerComponent.propTypes = {
  resource: T.shape({
    meta: T.shape({
      mimeType: T.string.isRequired
    }).isRequired
  }).isRequired,
  url: T.string.isRequired,
  tracks: T.arrayOf(T.shape(TrackTypes.propTypes)),
  canDownload: T.bool.isRequired
}

const Player = connect(
  state => ({
    resource: resourceSelect.resourceNode(state),
    url: state.url,
    tracks: state.tracks,
    canDownload: hasPermission('export', resourceSelect.resourceNode(state))
  })
)(PlayerComponent)

export {
  Player
}