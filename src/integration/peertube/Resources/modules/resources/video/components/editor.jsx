import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {EditorPage} from '#/main/app/editor'
import {ResourceEditor} from '#/main/core/resource/editor'

import {selectors} from '#/integration/peertube/resources/video/store/selectors'

const VideoEditorParameters = () =>
  <EditorPage
    title={trans('parameters')}
    definition={[
      {
        title: trans('general'),
        primary: true,
        hideTitle: true,
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
        primary: true,
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

const VideoEditor = () => {
  const video = useSelector(selectors.video)

  return (
    <ResourceEditor
      additionalData={() => ({
        resource: video
      })}
      pages={[
        {
          name: 'parameters',
          title: trans('parameters'),
          component: VideoEditorParameters
        }
      ]}
    />
  )
}

export {
  VideoEditor
}
