import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {EditorPage} from '#/main/app/editor'

import {selectors} from '#/integration/youtube/resources/video/store/selectors'
import {ResourceEditor} from '#/main/core/resource/editor'

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
        title: trans('playback_param', {}, 'youtube'),
        help: trans('timecode_help',{}, 'youtube'),
        primary: true,
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

const VideoEditor = () => {
  const video = useSelector(selectors.video)

  return (
    <ResourceEditor
      styles={['claroline-distribution-integration-youtube-youtube']}
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
