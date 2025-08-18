import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormContent} from '#/main/app/content/form/containers/content'

import {ItemEditor as ItemEditorTypes} from '#/plugin/exo/items/prop-types'

const AudioEditor = props =>
  <FormContent
    className="audio-item audio-editor"
    name={props.formName}
    definition={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: '_file',
            label: trans('file'),
            type: 'file',
            required: true,
            calculated: (item) => item.url ? ({
              url: item.url,
              mimeType: item.type
            }) : null,
            onChange: (file) => {
              props.update('url', file.url)
              props.update('type', file.mimeType)
            },
            options: {
              types: ['audio/*']
            }
          }
        ]
      }
    ]}
  />

implementPropTypes(AudioEditor, ItemEditorTypes, {
  item: T.shape(

  ).isRequired
})

export {
  AudioEditor
}
