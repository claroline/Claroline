import get from 'lodash/get'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'

/**
 * Unarchives some workspaces.
 *
 * @param {Array}  workspaces - the list of workspaces on which we want to execute the action.
 * @param {object} refresher  - an object containing methods to update context in response to action (eg. add, update, delete).
 */
export default (workspaces, refresher) => {
  const processable = workspaces.filter(workspace => hasPermission('archive', workspace) && get(workspace, 'meta.archived'))

  return {
    name: 'unarchive',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-box-open',
    label: trans('unarchive', {}, 'actions'),
    displayed: 0 !== processable.length,
    request: {
      url: url(
        ['apiv2_workspace_unarchive'],
        {ids: processable.map(workspace => workspace.id)}
      ),
      request: {
        method: 'PUT'
      },
      success: (response) => refresher.update(response)
    },
    group: trans('management'),
    scope: ['object', 'collection'],
    primary: true
  }
}
