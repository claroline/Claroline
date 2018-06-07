import {trans} from '#/main/core/translation'
import {isAuthenticated} from '#/main/core/user/current'

const action = (resourceNodes) => ({ // todo collection
  name: 'favourite',
  type: 'async',
  icon: 'fa fa-fw fa-star-o',
  label: trans('add-favourite', {}, 'actions'),
  displayed: isAuthenticated(),
  request: {
    url: ['claro_resource_action', {
      resourceType: resourceNodes[0].meta.type,
      action: 'favourite',
      id: resourceNodes[0].id
    }],
    request: {
      method: 'POST'
    }
  }
})

export {
  action
}
