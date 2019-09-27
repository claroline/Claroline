import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {route as desktopRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {BadgeCard} from '#/plugin/open-badge/tools/badges/badge/components/card'

export default {
  name: 'badges',
  icon: 'fa fa-fw fa-trophy',
  parameters: {
    primaryAction: (badge) => ({
      type: URL_BUTTON,
      target: badge.workspace ?
        `#${desktopRoute('open-badge')}/badges/${badge.id}` :
        `#${workspaceRoute(badge.workspace, 'open-badge')}/badges/${badge.id}`
    }),
    definition: [
      {
        name: 'name',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'meta.enabled',
        label: trans('enabled'),
        type: 'boolean',
        displayed: true
      }, {
        name: 'assignable',
        label: trans('assignable', {}, 'badge'),
        type: 'boolean',
        displayed: false,
        displayable: false,
        filterable: true
      }, {
        name: 'workspace',
        label: trans('workspace'),
        type: 'workspace',
        displayed: true,
        filterable: true
      }
    ],
    card: BadgeCard
  }
}
