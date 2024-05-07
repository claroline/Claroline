import React from 'react'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {EditorActions} from '#/main/app/editor'

const ToolEditorActions = (props) =>
  <EditorActions
    actions={(props.actions || []).concat([
      {
        title: trans('Désactiver l\'outil'),
        help: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
        action: {
          label: trans('disable', {}, 'actions'),
          type: CALLBACK_BUTTON,
          callback: () => true
        },
        dangerous: true,
        managerOnly: true
      }
    ])}
  />

ToolEditorActions.propTypes = {
  actions: T.array
}

export {
  ToolEditorActions
}
