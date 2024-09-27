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
        name: 'meta.description',
        type: 'string',
        label: trans('description'),
        options: {long: true},
        sortable: false
      }, {
        name: 'meta.createdAt',
        type: 'date',
        label: trans('creation_date'),
        options: {time: true},
        filterable: false,
        sortable: false
      }, {
        name: 'meta.updatedAt',
        type: 'date',
        label: trans('modification_date'),
        options: {time: true},
        filterable: false,
        sortable: false
      }, {
        name: 'meta.archived',
        alias: 'archived',
        label: trans('archived'),
        type: 'boolean'
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
