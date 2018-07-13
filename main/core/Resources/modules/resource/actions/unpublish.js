import get from 'lodash/get'

import {number} from '#/main/app/intl'
import {trans} from '#/main/core/translation'

const action = (resourceNodes, refreshNodes) => ({ // todo collection
  name: 'unpublish',
  type: 'async',
  icon: 'fa fa-fw fa-eye-slash',
  label: trans('unpublish', {}, 'actions'),
  displayed: -1 !== resourceNodes.findIndex(node => !!get(node, 'meta.published')),
  subscript: 1 === resourceNodes.length ? {
    type: 'label',
    status: 'primary',
    value: number(get(resourceNodes[0], 'meta.views') || 0, true)
  } : undefined,
  request: {
    type: 'unpublish',
    url: ['claro_resource_node_unpublish', {ids: resourceNodes.map(node => node.id)}],
    request: {
      method: 'PUT'
    },
    success: (response) => refreshNodes(response)
  }
})

export {
  action
}
