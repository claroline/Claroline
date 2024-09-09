import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

const DesktopEditorOverview = () =>
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
            readOnly: true,
            calculated: () => trans('desktop', {}, 'context')
          }
        ]
      }
    ]}
  />

export {
  DesktopEditorOverview
}
