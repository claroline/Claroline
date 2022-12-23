import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ResourcePage} from '#/main/core/resource/containers/page'

import {Video as VideoTypes} from '#/integration/peertube/prop-types'
import {VideoPlayer} from '#/integration/peertube/resources/video/containers/player'
import {VideoEditor} from '#/integration/peertube/resources/video/containers/editor'

const VideoResource = props =>
  <ResourcePage
    routes={[
      {
        path: '/',
        exact: true,
        component: VideoPlayer
      }, {
        path: '/edit',
        component: VideoEditor,
        onEnter: () => props.resetForm(props.video)
      }
    ]}
  />

VideoResource.propTypes = {
  path: T.string.isRequired,
  video: T.shape(
    VideoTypes.propTypes
  ),
  resetForm: T.func.isRequired
}

export {
  VideoResource
}
