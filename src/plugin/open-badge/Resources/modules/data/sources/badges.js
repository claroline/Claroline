import React from 'react'
import {trans} from '#/main/app/intl/translation'

import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {BadgeCard} from '#/plugin/open-badge/badge/components/card'
import {BadgeImage} from '#/plugin/open-badge/badge/components/image'

export default (contextType, contextData, refresher, currentUser) => {
  let basePath
  if ('workspace' === contextType) {
    basePath = workspaceRoute(contextData, 'community')
  } else {
    basePath = toolRoute('community')
  }

  return {
    primaryAction: (badge) => getDefaultAction(badge, refresher, basePath, currentUser),
    actions: (badges) => getActions(badges, refresher, basePath, currentUser),
    definition: [
      {
        name: 'name',
        label: trans('name'),
        displayed: true,
        primary: true,
        render: (badge) => (
          <div className="d-flex flex-direction-row gap-3 align-items-center">
            <BadgeImage badge={badge} size="xs" />
            {badge.name}
          </div>
        )
      }, {
        name: 'meta.enabled',
        label: trans('enabled'),
        type: 'boolean'
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
        filterable: true
      }, {
        name: 'tags',
        type: 'tag',
        label: trans('tags'),
        sortable: false,
        options: {
          objectClass: 'Claroline\\OpenBadgeBundle\\Entity\\BadgeClass'
        }
      }, {
        name: 'organizations',
        type: 'organizations',
        label: trans('organizations'),
        displayable: false,
        displayed: false,
        sortable: false,
        filterable: true
      }
    ],
    card: BadgeCard
  }
}
