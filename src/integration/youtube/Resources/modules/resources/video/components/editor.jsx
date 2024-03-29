import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/integration/youtube/resources/video/store/selectors'

const VideoEditor = (props) =>
  <FormData
    level={2}
    title={trans('parameters')}
    name={selectors.FORM_NAME}
    buttons={true}
    target={['apiv2_youtube_video_update', { id: props.video.id }]}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    definition={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'url',
            label: trans('url'),
            type: 'url',
            required: true
          }
        ]
      }, {
        icon: 'fa fa-fw fa-play',
        title: trans('playback_param', {}, 'youtube'),
        help: trans('timecode_help',{}, 'youtube'),
        fields: [
          {
            name: 'timecodeStart',
            label: trans('timecode_start', {}, 'youtube'),
            type: 'time'
          }, {
            name: 'timecodeEnd',
            label: trans('timecode_end', {}, 'youtube'),
            type: 'time'
          }, {
            name: 'autoplay',
            label: trans('autoplay', {}, 'youtube'),
            type: 'boolean'
          }, {
            name: 'looping',
            label: trans('loop', {}, 'youtube'),
            type: 'boolean'
          }, {
            name: 'controls',
            label: trans('controls', {}, 'youtube'),
            type: 'boolean'
          }, {
            name: 'resume',
            label: trans('resume', {}, 'youtube'),
            type: 'boolean'
          }
        ]
      }
    ]}
  />

VideoEditor.propTypes = {
  path: T.string.isRequired,
  video: T.shape({
    id: T.string.isRequired
  }).isRequired
}

export {
  VideoEditor
}
