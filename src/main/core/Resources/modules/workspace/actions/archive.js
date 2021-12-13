import get from 'lodash/get'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'
import {trans, transChoice} from '#/main/app/intl/translation'

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
      title: trans('workspaces_archive_title'),
      message: transChoice('workspaces_archive_question', processable.length, {count: processable.length})
    },
    request: {
      url: url(
        ['apiv2_workspace_archive'],
        {ids: processable.map(workspace => workspace.id)}
      ),
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
