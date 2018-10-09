import {trans} from '#/main/core/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {WorkspaceCard} from '#/main/core/workspace/data/components/workspace-card'

const WorkspaceList = {
  open: (row) => ({
    label: trans('open'),
    type: URL_BUTTON,
    target: ['claro_workspace_open', {workspaceId: row.id}]
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
      name: 'meta.model',
      label: trans('model'),
      type: 'boolean',
      alias: 'model',
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
      name: 'display.displayable',
      label: trans('displayable_in_workspace_list'),
      type: 'boolean',
      alias: 'displayable'
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
      name: 'registration.selfUnregistration',
      label: trans('public_unregistration'),
      type: 'boolean',
      alias: 'selfUnregistration'
    }, {
      name: 'restrictions.maxStorage',
      label: trans('max_storage_size'),
      alias: 'maxStorage'
    }, {
      name: 'restrictions.maxResources',
      label: trans('max_amount_resources'),
      type: 'number',
      alias: 'maxResources'
    }, {
      name: 'restrictions.maxUsers',
      label: trans('workspace_max_users'),
      type: 'number',
      alias: 'maxUsers'
    }
  ],
  card: WorkspaceCard
}

export {
  WorkspaceList
}
