import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {route as desktopRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {ToolCard} from '#/main/core/tool/components/card'

export default {
  name: 'tools',
  icon: 'fa fa-fw fa-tools',
  parameters: {
    primaryAction: (tool) => ({
      type: URL_BUTTON,
      target: 'desktop' === tool.context.type ?
        `#${desktopRoute(tool.name)}` :
        `#${workspaceRoute(tool.context.data, tool.name)}`
    }),
    definition: [
      {
        name: 'name',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'display.order',
        alias: 'order',
        type: 'number',
        label: trans('order'),
        displayable: false,
        filterable: false,
        sortable: true
      }, {
        name: 'restrictions.hidden',
        alias: 'hidden',
        type: 'boolean',
        label: trans('hidden'),
        displayable: false,
        filterable: true,
        sortable: false
      }
    ],
    card: ToolCard
  }
}
