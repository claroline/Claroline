import {createElement} from 'react'
import get from 'lodash/get'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'
import {trans, transChoice} from '#/main/app/intl/translation'

import {WorkspaceCard} from '#/main/core/workspace/components/card'

/**
 * Archives some workspaces.
 *
 * @param {Array}  workspaces - the list of workspaces on which we want to execute the action.
 * @param {object} refresher  - an object containing methods to update context in response to action (eg. add, update, delete).
 */
export default (workspaces, refresher) => {
  const processable = workspaces.filter(workspace => hasPermission('archive', workspace) && !get(workspace, 'meta.archived'))

  return {
    name: 'archive',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-box',
    label: trans('archive', {}, 'actions'),
    displayed: 0 !== processable.length,
    confirm: {
      title: transChoice('archive_confirm_title', processable.length, {}, 'workspace'),
      subtitle: 1 === processable.length ? processable[0].name : transChoice('count_elements', processable.length, {count: processable.length}),
      message: transChoice('archive_confirm_message', processable.length, {count: processable.length}, 'workspace'),
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
      url: url(['apiv2_workspace_archive'], {ids: processable.map(workspace => workspace.id)}),
      request: {
        method: 'PUT'
      },
      success: (response) => refresher.update(response)
    },
    group: trans('management'),
    scope: ['object', 'collection'],
    dangerous: true
  }
}
