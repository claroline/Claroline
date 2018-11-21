import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

export default (resourceNodes) => ({
  name: 'open_survey',
  type: URL_BUTTON,
  label: trans('open', {}, 'actions'),
  icon: 'fa fa-fw fa-door-open',
  primary: true,
  target: ['claro_survey_node_index', {
    node: resourceNodes[0].id
  }]
})
