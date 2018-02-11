import {t} from '#/main/core/translation'
import {generateUrl} from '#/main/core/api/router'

import {WorkspaceCard} from '#/main/core/administration/workspace/workspace/components/workspace-card.jsx'

const WorkspaceList = {
  open: {
    action: (row) => generateUrl('claro_workspace_open', {workspaceId: row.id})
  },
  definition: [
    {
      name: 'name',
      label: t('name'),
      displayed: true,
      primary: true
    }, {
      name: 'code',
      label: t('code'),
      displayed: true
    }, {
      name: 'meta.model',
      label: t('model'),
      type: 'boolean',
      alias: 'model',
      displayed: true
    }, {
      name: 'meta.created',
      label: t('creation_date'),
      type: 'date',
      alias: 'created',
      displayed: true,
      filterable: false
    }, {
      name: 'meta.personal',
      label: t('personal_workspace'),
      type: 'boolean',
      alias: 'personal'
    }, {
      name: 'display.displayable',
      label: t('displayable_in_workspace_list'),
      type: 'boolean',
      alias: 'displayable'
    }, {
      name: 'createdAfter',
      label: t('created_after'),
      type: 'date',
      displayable: false
    }, {
      name: 'createdBefore',
      label: t('created_before'),
      type: 'date',
      displayable: false
    }, {
      name: 'registration.selfRegistration',
      label: t('public_registration'),
      type: 'boolean',
      alias: 'selfRegistration'
    }, {
      name: 'registration.selfUnregistration',
      label: t('public_unregistration'),
      type: 'boolean',
      alias: 'selfUnregistration'
    }, {
      name: 'restrictions.maxStorage',
      label: t('max_storage_size'),
      alias: 'maxStorage'
    }, {
      name: 'restrictions.maxResources',
      label: t('max_amount_resources'),
      type: 'number',
      alias: 'maxResources'
    }, {
      name: 'restrictions.maxUsers',
      label: t('workspace_max_users'),
      type: 'number',
      alias: 'maxUsers'
    }
  ],
  card: WorkspaceCard
}

export {
  WorkspaceList
}
