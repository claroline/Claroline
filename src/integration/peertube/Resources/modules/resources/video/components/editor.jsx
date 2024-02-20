import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/integration/peertube/resources/video/store/selectors'

const VideoEditor = (props) =>
  <FormData
    level={2}
    title={trans('parameters')}
    name={selectors.FORM_NAME}
    buttons={true}
    target={['apiv2_peertube_video_update', {id: props.video.id}]}
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
        title: trans('playback_param', {}, 'peertube'),
        help: trans('timecode_help',{}, 'peertube'),
        fields: [
          {
            name: 'timecodeStart',
            label: trans('timecode_start', {}, 'peertube'),
            type: 'time'
          }, {
            name: 'timecodeEnd',
            label: trans('timecode_end', {}, 'peertube'),
            type: 'time'
          }, {
            name: 'autoplay',
            label: trans('autoplay', {}, 'peertube'),
            type: 'boolean'
          }, {
            name: 'looping',
            label: trans('loop', {}, 'peertube'),
            type: 'boolean'
          }, {
            name: 'controls',
            label: trans('controls', {}, 'peertube'),
            type: 'boolean'
          }, {
            name: 'peertubeLink',
            label: trans('peertubeLink', {}, 'peertube'),
            type: 'boolean'
          }, {
            name: 'resume',
            label: trans('resume', {}, 'peertube'),
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
