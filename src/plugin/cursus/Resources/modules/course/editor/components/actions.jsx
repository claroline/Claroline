import React from 'react'

import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {EditorActions} from '#/main/app/editor'

const CourseEditorActions = () =>
  <EditorActions
    actions={[
      {
        title: trans('archive_training', {}, 'actions'),
        help: trans('archive_training_help', {}, 'actions'),
        action: {
          label: trans('archive', {}, 'actions'),
          type: CALLBACK_BUTTON,
          callback: () => false
        },
        managerOnly: true
      }, {
        title: trans('restore_training', {}, 'actions'),
        help: trans('restore_training_help', {}, 'actions'),
        action: {
          label: trans('restore', {}, 'actions'),
          type: CALLBACK_BUTTON,
          callback: () => false
        },
        managerOnly: true
      }, {
        title: trans('delete_training', {}, 'actions'),
        help: trans('delete_training_help', {}, 'actions'),
        action: {
          label: trans('delete', {}, 'actions'),
          type: CALLBACK_BUTTON,
          callback: () => false
        },
        dangerous: true,
        managerOnly: true
      }
    ]}
  />

export {
  CourseEditorActions
}
