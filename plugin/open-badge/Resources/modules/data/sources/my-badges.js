import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {route as desktopRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {AssertionCard} from '#/plugin/open-badge/tools/badges/assertion/components/card'

export default {
  name: 'my_badges',
  icon: 'fa fa-fw fa-trophy',
  parameters: {
    primaryAction: (assertion) => ({
      type: URL_BUTTON,
      target: get(assertion, 'badge.workspace') ?
        `#${desktopRoute('open-badge')}/badges/${get(assertion, 'badge.id')}/assertion/${assertion.id}` :
        `#${workspaceRoute(get(assertion, 'badge.workspace'), 'open-badge')}/badges/${get(assertion, 'badge.id')}/assertion/${assertion.id}`
    }),
    definition: [
      {
        name: 'badge.name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'badge.meta.enabled',
        type: 'boolean',
        label: trans('enabled', {}, 'badge'),
        displayed: true
      }
    ],
    card: AssertionCard
  }
}
