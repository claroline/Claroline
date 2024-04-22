import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Resource} from '#/main/core/resource'

import {Video as VideoTypes} from '#/integration/youtube/prop-types'
import {VideoPlayer} from '#/integration/youtube/resources/video/containers/player'
import {VideoEditor} from '#/integration/youtube/resources/video/containers/editor'

const VideoResource = props =>
  <Resource
    {...omit(props, 'video', 'resetForm')}
    styles={['claroline-distribution-integration-youtube-youtube']}
    pages={[
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
  video: T.shape(
    VideoTypes.propTypes
  ),
  resetForm: T.func.isRequired
}

export {
  VideoResource
}
