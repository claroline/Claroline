import {trans} from '#/main/core/translation'

const action = (resourceNodes) => ({ // todo collection
  name: 'export',
  type: 'async',
  icon: 'fa fa-fw fa-download',
  label: trans('export', {}, 'actions'),
  request: {
    url: ['claro_resource_action', {
      resourceType: resourceNodes[0].meta.type,
      action: 'export',
      id: resourceNodes[0].id
    }]
  }
})

export {
  action
}
