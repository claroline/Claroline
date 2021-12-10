import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/workspace/routing'
import {WorkspaceCard} from '#/main/core/workspace/components/card'

export default {
  name: 'workspaces',
  icon: 'fa fa-fw fa-book',
  parameters: {
    primaryAction: (workspace) => ({
      type: URL_BUTTON,
      target: `#${route(workspace)}`
    }),
    definition: [
      {
        name: 'name',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'code',
        label: trans('code'),
        displayed: true
      }, {
        name: 'meta.created',
        label: trans('creation_date'),
        type: 'date',
        alias: 'createdAt',
        displayed: true,
        filterable: false
      }, {
        name: 'meta.updated',
        label: trans('modification_date'),
        type: 'date',
        alias: 'updatedAt',
        filterable: false
      }, {
        name: 'meta.personal',
        label: trans('personal_workspace'),
        type: 'boolean',
        alias: 'personal'
      }, {
        name: 'createdAfter',
        label: trans('created_after'),
        type: 'date',
        displayable: false
      }, {
        name: 'createdBefore',
        label: trans('created_before'),
        type: 'date'
      }, {
        name: 'registration.selfRegistration',
        label: trans('public_registration'),
        type: 'boolean',
        alias: 'selfRegistration'
      }, {
        name: 'registration.waitingForRegistration',
        label: trans('pending'),
        type: 'boolean',
        filterable: false,
        sortable: false
      }, {
        name: 'archived',
        label: trans('archived'),
        type: 'boolean',
        filterable: true,
        displayable: false
      }, {
        name: 'tags',
        type: 'tag',
        label: trans('tags'),
        displayable: false,
        sortable: false,
        options: {
          objectClass: 'Claroline\\CoreBundle\\Entity\\Workspace\\Workspace'
        }
      }
    ],
    card: WorkspaceCard
  }
}
