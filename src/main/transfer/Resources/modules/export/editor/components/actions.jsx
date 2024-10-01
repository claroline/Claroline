import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorActions} from '#/main/app/editor'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

const ExportEditorActions = () =>
  <EditorActions
    actions={[
      {
        title: trans('unblock_export', {}, 'actions'),
        help: trans('unblock_export_help', {}, 'actions'),
        action: {
          label: trans('unblock', {}, 'actions'),
          type: CALLBACK_BUTTON,
          callback: () => false
        }
      }, {
        title: trans('delete_export', {}, 'actions'),
        help: trans('delete_export_help', {}, 'actions'),
        action: {
          label: trans('delete', {}, 'actions'),
          type: CALLBACK_BUTTON,
          callback: () => false
        },
        dangerous: true
      }
    ]}
  />

export {
  ExportEditorActions
}
