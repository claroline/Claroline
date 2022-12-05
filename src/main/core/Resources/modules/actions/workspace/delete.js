import {createElement} from 'react'

import {hasPermission} from '#/main/app/security'
import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'

import {WorkspaceCard} from '#/main/core/workspace/components/card'

/**
 * Delete workspaces action.
 */
export default (workspaces, refresher) => {
  const processable = workspaces
    .filter(w => hasPermission('delete', w) && w.code !== 'default_personal' && w.code !== 'default_workspace' && w.meta.archived)

  return {
    name: 'delete',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-trash',
    label: trans('delete', {}, 'actions'),
    displayed: 0 !== processable.length,
    dangerous: true,
    confirm: {
      title: transChoice('delete_confirm_title', processable.length, {}, 'workspace'),
      subtitle: 1 === processable.length ? processable[0].name : transChoice('count_elements', processable.length, {count: processable.length}),
      message: transChoice('delete_confirm_message', processable.length, {count: processable.length}, 'workspace'),
      additional: [
        createElement('div', {
          key: 'additional',
          className: 'modal-body'
        }, processable.map(workspace => createElement(WorkspaceCard, {
          key: workspace.id,
          orientation: 'row',
          size: 'xs',
          data: workspace
        })))
      ]
    },
    request: {
      url: url(['apiv2_workspace_delete_bulk'], {ids: processable.map(w => w.id)}),
      request: {
        method: 'DELETE'
      },
      success: () => refresher.delete(processable)
    },
    group: trans('management'),
    scope: ['object', 'collection']
  }
}
