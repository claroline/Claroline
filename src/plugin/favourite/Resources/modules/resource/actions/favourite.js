import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default (resourceNodes, nodesRefresher, path, currentUser) => ({
  name: 'favourite',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-star',
  label: trans('add-favourite', {}, 'actions'),
  displayed: !isEmpty(currentUser),
  request: {
    url: ['hevinci_favourite_resources_toggle', {ids: resourceNodes.map(node => node.id)}],
    request: {
      method: 'PUT'
    }
  }
})
