import {trans} from '#/main/core/translation'
import {URL_BUTTON} from '#/main/app/buttons'

const action = (resourceNodes) => ({ // TODO : collection
  name: 'download',
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-download',
  label: trans('download', {}, 'actions'),
  url: ['claro_resource_action', {
    type: resourceNodes[0].meta.type,
    action: 'download',
    id: resourceNodes[0].id
  }]
})

export {
  action
}
