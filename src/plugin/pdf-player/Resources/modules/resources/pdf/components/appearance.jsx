import React from 'react'
import {trans} from '#/main/app/intl'
import {ResourceEditorAppearance} from '#/main/core/resource/editor/components/appearance'

const PdfEditorAppearance = () =>
  <ResourceEditorAppearance
    definition={[
      {
        name: 'Pdf-display',
        icon: 'fa fa-fw fa-desktop',
        title: trans('scroll_mode', {}, 'resource'),
        primary: true,
        hideTitle: true,
        fields: [
          {
            name: 'resource.display.scrollMode',
            type: 'choice',
            label: trans('scroll_mode', {}, 'resource'),
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: {
                PAGE: trans('scroll_page', {}, 'resource'),
                VERTICAL: trans('scroll_vertical', {}, 'resource'),
                HORIZONTAL: trans('scroll_horizontal', {}, 'resource')
              }
            }
          }
        ]
      }
    ]}
  />

export {
  PdfEditorAppearance
}
