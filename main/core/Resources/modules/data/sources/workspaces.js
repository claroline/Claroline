import {trans} from '#/main/core/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {WorkspaceCard} from '#/main/core/workspace/data/components/workspace-card'

export default {
  name: 'workspaces',
  icon: 'fa fa-fw fa-book',
  parameters: {
    primaryAction: (workspace) => ({
      type: URL_BUTTON,
      target: ['claro_workspace_open', {
        workspaceId: workspace.id
      }]
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
        alias: 'created',
        displayed: true,
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
        type: 'date',
        displayable: false
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
      }
    ],
    card: WorkspaceCard
  }
}
