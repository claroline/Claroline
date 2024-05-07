import React from 'react'

import {trans} from '#/main/app/intl'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {ToolEditor} from '#/main/core/tool'
import {ToolEditorActions} from '#/main/core/tool/editor'

import {MODAL_TRANSFER} from '#/plugin/open-badge/modals/transfer'

const BadgesToolActions = () =>
  <ToolEditorActions
    actions={[
      {
        title: trans('transfer_badges', {}, 'actions'),
        help: trans('Transférez tous les badges d\'un utilisateur à un autre.', {}, 'badge'),
        managerOnly: true,
        action: {
          name: 'transfer-badges',
          type: MODAL_BUTTON,
          label: trans('transfer', {}, 'actions'),
          modal: [MODAL_TRANSFER]
        }
      }
    ]}
  />

const BadgesToolEditor = (props) =>
  <ToolEditor
    actionsPage={BadgesToolActions}
  />

export {
  BadgesToolEditor
}