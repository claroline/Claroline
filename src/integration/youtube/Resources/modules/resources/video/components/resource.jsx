import React from 'react'

import {Resource} from '#/main/core/resource'

import {VideoPlayer} from '#/integration/youtube/resources/video/containers/player'
import {VideoEditor} from '#/integration/youtube/resources/video/components/editor'

const VideoResource = (props) =>
  <Resource
    {...props}
    styles={['claroline-distribution-integration-youtube-youtube']}
    editor={VideoEditor}
    pages={[
      {
        path: '/',
        exact: true,
        component: VideoPlayer
      }
    ]}
  />

export {
  VideoResource
}
