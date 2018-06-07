import {trans} from '#/main/core/translation'

const action = (resourceNodes) => ({ // todo collection
  name: 'delete',
  type: 'async',
  icon: 'fa fa-fw fa-trash-o',
  label: trans('delete', {}, 'actions'),
  dangerous: true,
  confirm: {
    title: trans('resources_delete_confirm'),
    message: trans('resources_delete_message')
  },
  request: {
    url: ['claro_resource_action', {
      resourceType: resourceNodes[0].meta.type,
      action: 'delete',
      id: resourceNodes[0].id
    }],
    request: {
      method: 'DELETE'
    }
  }
})

export {
  action
}
