import {createElement} from 'react'
import get from 'lodash/get'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'
import {isAdmin} from '#/main/app/security/permissions'

import {WorkspaceCard} from '#/main/core/workspace/components/card'

/**
 * Let the current user unregister himself from some workspaces.
 */
export default (workspaces, refresher, path, currentUser) => {
  const processable = workspaces.filter(workspace => !!currentUser &&
    workspace.registered && (get(workspace, 'registration.selfUnregistration') || isAdmin(currentUser)))

  return ({
    name: 'unregister-self',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-sign-out',
    label: trans('self_unregister', {}, 'actions'),
    displayed: 0 !== processable.length,
    confirm: {
      title: transChoice('self_unregister_confirm_title', processable.length, {}, 'workspace'),
      subtitle: 1 === processable.length ? processable[0].name : transChoice('count_elements', processable.length, {count: processable.length}),
      message: transChoice('self_unregister_confirm_message', processable.length, {count: processable.length}, 'workspace'),
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
      url: url(['apiv2_workspace_unregister', {user: get(currentUser, 'id')}], {workspaces: processable.map(workspace => workspace.id)}),
      request: {
        method: 'DELETE'
      },
      success: (response) => refresher.update(response)
    },
    dangerous: true,
    group: trans('registration'),
    scope: ['object', 'collection']
  })
}
