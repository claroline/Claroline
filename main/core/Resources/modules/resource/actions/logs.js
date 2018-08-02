import {trans} from '#/main/core/translation'
import {URL_BUTTON} from '#/main/app/buttons'

const action = (resourceNodes) => ({
  name: 'logs',
  type: URL_BUTTON, // TODO : it will be section
  icon: 'fa fa-fw fa-line-chart',
  label: trans('show-logs', {}, 'actions'),
  target: ['claro_resource_logs', {node: resourceNodes[0].id}]
})

export {
  action
}
