import React from 'react'

import {ResourceEditorAppearance} from '#/main/core/resource/editor/components/appearance'
import {trans} from '#/main/app/intl'

const SlideshowEditorAppearance = () =>
  <ResourceEditorAppearance
    definition={[
      {
        name: 'play',
        //icon: 'fa fa-fw fa-play',
        title: trans('playback'),
        primary: true,
        fields: [
          {
            name: 'resource.display.showControls',
            type: 'boolean',
            label: trans('show_controls', {}, 'slideshow')
          }, {
            name: 'resource.interval',
            type: 'number',
            label: trans('slide_duration', {}, 'slideshow'),
            options: {
              unit: 'ms'
            }
          }, {
            name: 'resource.autoPlay',
            type: 'boolean',
            label: trans('auto_play', {}, 'slideshow')
          }
        ]
      }
    ]}
  />

export {
  SlideshowEditorAppearance
}