import {WidgetsTab} from '#/plugin/home/home/widgets/components/tab'
import {WidgetsTabParameters} from '#/plugin/home/home/widgets/components/parameters'

export default {
  name: 'widgets',
  icon: 'fa fa-fw fa-th-large',
  class: 'Claroline\\HomeBundle\\Entity\\Type\\WidgetsTab', // TODO : declare it in API
  component: WidgetsTab,
  parameters: WidgetsTabParameters
}
