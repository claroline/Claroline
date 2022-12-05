import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (workspaces, workspacesRefresher, path, currentUser) => ({
  name: 'favourite',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-star',
  label: trans('add-favourite', {}, 'actions'),
  displayed: !isEmpty(currentUser),
  request: {
    url: ['hevinci_favourite_workspaces_toggle', {ids: workspaces.map(workspace => workspace.id)}],
    request: {
      method: 'PUT'
    }
  }
})
