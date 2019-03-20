import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

import {ItemEditor as ItemEditorTypes} from '#/plugin/exo/items/prop-types'

const VideoEditor = props =>
  <FormData
    className="audio-item audio-editor"
    embedded={true}
    name={props.formName}
    dataPart={props.path}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'file',
            label: trans('file'),
            type: 'file',
            required: true
          }
        ]
      }
    ]}
  />

implementPropTypes(VideoEditor, ItemEditorTypes, {
  item: T.shape(

  ).isRequired
})

export {
  VideoEditor
}
