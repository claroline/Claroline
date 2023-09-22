import {trans} from '#/main/app/intl/translation'

import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {constants} from '#/main/community/constants'
import {RoleCard} from '#/main/community/role/components/card'
import {getActions, getDefaultAction} from '#/main/community/role/utils'

export default (contextType, contextData, refresher, currentUser) => {
  let basePath
  if ('workspace' === contextType) {
    basePath = workspaceRoute(contextData, 'community')
  } else {
    basePath = toolRoute('community')
  }

  return {
    primaryAction: (role) => getDefaultAction(role, refresher, basePath, currentUser),
    actions: (roles) => getActions(roles, refresher, basePath, currentUser),
    definition: [
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'type',
        type: 'choice',
        label: trans('type'),
        displayed: true,
        options: {
          choices: constants.ROLE_TYPES
        }
      }, {
        name: 'meta.description',
        type: 'string',
        label: trans('description'),
        options: {long: true},
        displayed: true,
        sortable: false
      }, {
        name: 'workspace',
        type: 'workspace',
        label: trans('workspace'),
        displayed: true
      }, {
        name: 'user',
        type: 'user',
        label: trans('user'),
        filterable: false
      }
    ],
    card: RoleCard
  }
}
