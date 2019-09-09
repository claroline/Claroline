import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/administration/routing'
import {ToolCard} from '#/main/core/tool/components/card'

export default {
  name: 'admin_tools',
  icon: 'fa fa-fw fa-tools',
  parameters: {
    primaryAction: (tool) => ({
      type: URL_BUTTON,
      target: `#${route(tool.name)}`
    }),
    definition: [
      {
        name: 'name',
        label: trans('name'),
        displayed: true,
        primary: true
      }
    ],
    card: ToolCard
  }
}
