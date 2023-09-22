import {trans} from '#/main/app/intl/translation'

import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {OrganizationCard} from '#/main/community/organization/components/card'
import {getActions, getDefaultAction} from '#/main/community/organization/utils'

export default (contextType, contextData, refresher, currentUser) => {
  let basePath
  if ('workspace' === contextType) {
    basePath = workspaceRoute(contextData, 'community')
  } else {
    basePath = toolRoute('community')
  }

  return {
    primaryAction: (organization) => getDefaultAction(organization, refresher, basePath, currentUser),
    actions: (organizations) => getActions(organizations, refresher, basePath, currentUser),
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
        label: trans('code')
      }, {
        name: 'meta.description',
        type: 'string',
        label: trans('description'),
        options: {long: true},
        displayed: true,
        sortable: false
      },{
        name: 'meta.default',
        type: 'boolean',
        label: trans('default')
      }, {
        name: 'email',
        type: 'email',
        label: trans('email')
      }, {
        name: 'parent',
        type: 'organization',
        label: trans('parent')
      }, {
        name: 'restrictions.public',
        alias: 'public',
        type: 'boolean',
        label: trans('public')
      }
    ],
    card: OrganizationCard
  }
}
