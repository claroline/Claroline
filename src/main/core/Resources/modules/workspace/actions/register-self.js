import {createElement} from 'react'
import get from 'lodash/get'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'
import {isAdmin} from '#/main/app/security/permissions'

import {WorkspaceCard} from '#/main/core/workspace/components/card'

/**
 * Let the current user register himself to some workspaces.
 */
export default (workspaces, refresher, path, currentUser) => {
  const processable = workspaces.filter(workspace => !workspace.registered && !workspace.meta.archived && !get(workspace, 'registration.waitingForRegistration')
    && (get(workspace, 'registration.selfRegistration') || isAdmin(currentUser)))

  return {
    name: 'register-self',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-sign-in',
    label: trans('self_register', {}, 'actions'),
    displayed: !!currentUser && 0 !== processable.length,
    confirm: {
      title: transChoice('self_register_confirm_title', processable.length, {}, 'workspace'),
      subtitle: 1 === processable.length ? processable[0].name : transChoice('count_elements', processable.length, {count: processable.length}),
      message: transChoice('self_register_confirm_message', processable.length, {count: processable.length}, 'workspace'),
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
      url: url(['apiv2_workspace_register', {user: get(currentUser, 'id')}], {workspaces: processable.map(workspace => workspace.id)}),
      request: {
        method: 'PATCH'
      },
      success: (response) => refresher.update(response)
    },
    group: trans('registration'),
    scope: ['object', 'collection'],
    primary: true
  }
}
