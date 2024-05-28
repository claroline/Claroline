import React from 'react'

import {Resource} from '#/main/core/resource'

import {VideoPlayer} from '#/integration/peertube/resources/video/containers/player'
import {VideoEditor} from '#/integration/peertube/resources/video/components/editor'

const VideoResource = props =>
  <Resource
    {...props}
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
