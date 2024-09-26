import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorActions} from '#/main/app/editor'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

const ImportEditorActions = () =>
  <EditorActions
    actions={[
      {
        title: trans('unblock_import', {}, 'actions'),
        help: trans('unblock_import_help', {}, 'actions'),
        action: {
          label: trans('unblock', {}, 'actions'),
          type: CALLBACK_BUTTON,
          callback: () => false
        }
      }, {
        title: trans('delete_import', {}, 'actions'),
        help: trans('delete_import_help', {}, 'actions'),
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
  ImportEditorActions
}
