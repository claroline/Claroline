import {trans} from '#/main/core/translation'

const action = (resourceNodes) => ({
  name: 'open',
  type: 'url',
  label: trans('open', {}, 'actions'),
  primary: true,
  icon: 'fa fa-fw fa-arrow-circle-o-right',
  target: ['claro_resource_open', {
    resourceType: resourceNodes[0].meta.type,
    node: resourceNodes[0].autoId
  }]
})

export {
  action
}
