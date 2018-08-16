import {trans} from '#/main/core/translation'
import {URL_BUTTON} from '#/main/app/buttons'

const action = (resourceNodes) => ({
  name: 'open',
  type: URL_BUTTON,
  label: trans('open', {}, 'actions'),
  primary: true,
  icon: 'fa fa-fw fa-arrow-circle-o-right',
  target: ['claro_resource_open', {
    resourceType: resourceNodes[0].meta.type,
    node: resourceNodes[0].id
  }]
})

export {
  action
}
