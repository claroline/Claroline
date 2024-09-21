import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'


export default (workspaces, workspacesRefresher, path, currentUser) => {
  const processable = workspaces.filter(workspace => !get(workspace, 'meta.model') && !get(workspace, 'meta.archived'))

  return {
    name: 'favourite',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-star',
    label: trans('add-favourite', {}, 'actions'),
    displayed: !isEmpty(currentUser) && 0 !== processable.length,
    request: {
      url: ['hevinci_favourite_workspaces_toggle', {ids: workspaces.map(workspace => workspace.id)}],
      request: {
        method: 'PUT'
      }
    }
  }
}
