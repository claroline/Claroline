import {trans} from '#/main/core/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

const action = (resourceNodes) => ({ // todo collection
  name: 'export',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-download',
  label: trans('export', {}, 'actions'),
  request: {
    url: ['claro_resource_action', {
      type: resourceNodes[0].meta.type,
      action: 'export',
      id: resourceNodes[0].id
    }]
  }
})

export {
  action
}
