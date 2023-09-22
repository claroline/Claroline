import {trans} from '#/main/app/intl/translation'

import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {TeamCard} from '#/main/community/team/components/card'
import {getActions, getDefaultAction} from '#/main/community/team/utils'

export default (contextType, contextData, refresher, currentUser) => {
  let basePath
  if ('workspace' === contextType) {
    basePath = workspaceRoute(contextData, 'community')
  } else {
    basePath = toolRoute('community')
  }

  return {
    primaryAction: (team) => getDefaultAction(team, refresher, basePath, currentUser),
    actions: (teams) => getActions(teams, refresher, basePath, currentUser),
    definition: [
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'meta.description',
        type: 'string',
        label: trans('description'),
        options: {long: true},
        displayed: true,
        sortable: false
      }, {
        name: 'users',
        type: 'number',
        label: trans('users'),
        displayed: true,
        filterable: false
      }, {
        name: 'directory',
        type: 'resource',
        label: trans('directory', {}, 'resource'),
        sortable: false,
        filterable: false
      }, {
        name: 'registration.selfRegistration',
        label: trans('public_registration'),
        displayed: false,
        filterable: true,
        type: 'boolean'
      }, {
        name: 'registration.selfUnregistration',
        label: trans('public_unregistration'),
        displayed: false,
        filterable: true,
        type: 'boolean'
      }
    ],
    card: TeamCard
  }
}
