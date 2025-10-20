import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

const PublicEditorOverview = () =>
  <EditorPage
    title={trans('overview')}
    definition={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'data.poster',
            type: 'poster',
            label: trans('poster'),
            hideLabel: true
          }, {
            name: 'data.name',
            type: 'string',
            label: trans('name'),
            required: true,
            disabled: true,
            calculated: () => trans('public', {}, 'context')
          }
        ]
      }
    ]}
  />

export {
  PublicEditorOverview
}
