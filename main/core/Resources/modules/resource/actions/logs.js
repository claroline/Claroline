import {trans} from '#/main/core/translation'

const action = (resourceNodes) => ({
  name: 'logs',
  type: 'url', // TODO : it will be section
  icon: 'fa fa-fw fa-line-chart',
  label: trans('show-logs', {}, 'actions'),
  target: ['claro_resource_logs', {node: resourceNodes[0].id}]
})

export {
  action
}
