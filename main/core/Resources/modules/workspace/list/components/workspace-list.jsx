import {t} from '#/main/core/translation'

import {WorkspaceCard} from '#/main/core/workspace/data/components/workspace-card'

const WorkspaceList = {
  open: (row) => ({
    type: 'url',
    target: ['claro_workspace_open', {workspaceId: row.id}]
  }),
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
      name: 'registration.waitingForRegistration',
      label: t('pending'),
      type: 'boolean',
      filterable: false,
      sortable: false
    }
  ],
  card: WorkspaceCard
}

export {
  WorkspaceList
}
