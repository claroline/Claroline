import {trans} from '#/main/app/intl/translation'

import {WorkspaceCard} from '#/main/core/workspace/components/card'
import {getActions, getDefaultAction} from '#/main/core/workspace/utils'

export default (contextType, contextData, refresher, currentUser) => ({
  primaryAction: (resourceNode) => getDefaultAction(resourceNode, refresher, null, currentUser),
  actions: (resourceNodes) => getActions(resourceNodes, refresher, null, currentUser),
  definition: [
    {
      name: 'name',
      type: 'string',
      label: trans('name'),
      displayed: true,
      primary: true
    }, {
      name: 'code',
      type: 'string',
      label: trans('code'),
      displayed: true
    }, {
      name: 'meta.description',
      type: 'string',
      label: trans('description'),
      sortable: false,
      options: {long: true}
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
    }, /*{
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
    }, */{
      name: 'archived',
      label: trans('archived'),
      type: 'boolean',
      filterable: true,
      displayable: false
    }, {
      name: 'restrictions.hidden',
      label: trans('hidden'),
      type: 'boolean',
      alias: 'hidden',
      filterable: true,
      displayable: false
    }, {
      name: 'tags',
      type: 'tag',
      label: trans('tags'),
      displayable: true,
      sortable: false,
      options: {
        objectClass: 'Claroline\\CoreBundle\\Entity\\Workspace\\Workspace'
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
  card: WorkspaceCard
})
