import get from 'lodash/get'

import {trans} from '#/main/core/translation'

const action = (resourceNodes) => ({ // todo collection
  name: 'unpublish',
  type: 'async',
  icon: 'fa fa-fw fa-eye-slash',
  label: trans('unpublish', {}, 'actions'),
  displayed: -1 !== resourceNodes.findIndex(node => !!get(node, 'meta.published')),
  subscript: 1 === resourceNodes.length && {
    type: 'label',
    status: 'primary',
    value: get(resourceNodes[0], 'meta.views')
  },
  request: {
    type: 'unpublish',
    url: ['claro_resource_node_unpublish', {ids: resourceNodes.map(node => node.id)}],
    request: {
      method: 'PUT'
    }
  }
})

export {
  action
}
