import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormContent} from '#/main/app/content/form/containers/content'

import {ItemEditor as ItemEditorTypes} from '#/plugin/exo/items/prop-types'

const TextEditor = props =>
  <FormContent
    className="audio-item audio-editor"
    name={props.formName}
    definition={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'data',
            label: trans('content'),
            type: 'html',
            required: true
          }
        ]
      }
    ]}
  />

implementPropTypes(TextEditor, ItemEditorTypes, {
  item: T.shape(

  ).isRequired
})

export {
  TextEditor
}
