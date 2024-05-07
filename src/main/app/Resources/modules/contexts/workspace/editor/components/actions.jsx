import React from 'react'

import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {EditorActions} from '#/main/app/editor'

const WorkspaceEditorActions = () =>
  <EditorActions
    actions={[
      {
        title: trans('Changer le propriétaire'),
        help: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
        action: {
          label: trans('Transférer', {}, 'actions'),
          type: CALLBACK_BUTTON,
          callback: () => true
        },
        managerOnly: true
      }, {
        title: trans('Archiver l\'espace d\'activités'),
        help: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
        action: {
          label: trans('archive', {}, 'actions'),
          type: CALLBACK_BUTTON,
          callback: () => true
        },
        dangerous: true,
        managerOnly: true
      }, {
        title: trans('Supprimer l\'espace d\'activités'),
        help: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
        action: {
          label: trans('delete', {}, 'actions'),
          type: CALLBACK_BUTTON,
          callback: () => true
        },
        dangerous: true,
        managerOnly: true
      }
    ]}
  />

export {
  WorkspaceEditorActions
}
