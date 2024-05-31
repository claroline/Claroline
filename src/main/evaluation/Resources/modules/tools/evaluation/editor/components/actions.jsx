import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {ASYNC_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'

import {selectors as toolSelectors} from '#/main/core/tool'
import {ToolEditorActions} from '#/main/core/tool/editor'

const EvaluationEditorActions = () => {
  const contextType = useSelector(toolSelectors.contextType)
  const contextId = useSelector(toolSelectors.contextId)

  return (
    <ToolEditorActions
      actions={[
        {
          title: trans('initialize_evaluations', {}, 'evaluation'),
          help: trans('Générez les évaluations pour tous les utilisateurs n\'ayant pas encore commencé l\'espace d\'activités.'),
          action: {
            name: 'initialize',
            type: ASYNC_BUTTON,
            label: trans('initialize', {}, 'actions'),
            displayed: 'workspace' === contextType,
            request: {
              url: ['apiv2_workspace_evaluations_init', {workspace: contextId}],
              request: {
                method: 'PUT'
              }
            }
          }
        }, {
          title: trans('recompute_evaluations', {}, 'evaluation'),
          help: trans('Recalculez les évaluations de tous les utilisateurs ayant commencé l\'espace d\'activités.'),
          action: {
            name: 'recompute',
            type: ASYNC_BUTTON,
            label: trans('recalculate', {}, 'actions'),
            displayed: 'workspace' === contextType,
            request: {
              url: ['apiv2_workspace_evaluations_recompute', {workspace: contextId}],
              request: {
                method: 'PUT'
              }
            }
          }
        }, {
          title: trans('Purger les évaluations'),
          help: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
          action: {
            label: trans('purge', {}, 'actions'),
            type: CALLBACK_BUTTON,
            callback: () => true
          },
          dangerous: true,
          managerOnly: true
        },
      ]}
    />
  )
}

export {
  EvaluationEditorActions
}
