import {trans} from '#/main/core/translation'
import {isAuthenticated} from '#/main/core/user/current'

const action = (resourceNodes) => ({ // todo collection
  name: 'favourite',
  type: 'async',
  icon: 'fa fa-fw fa-star-o',
  label: trans('add-favourite', {}, 'actions'),
  displayed: isAuthenticated(),
  request: {
    url: ['hevinci_favourite_toggle', {ids: resourceNodes.map(node => node.id)}],
    request: {
      method: 'PUT'
    }
  }
})

export {
  action
}
