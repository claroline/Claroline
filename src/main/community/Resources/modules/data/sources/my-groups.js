import {trans} from '#/main/app/intl/translation'

import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {GroupCard} from '#/main/community/group/components/card'
import {getActions, getDefaultAction} from '#/main/community/group/utils'

export default  (contextType, contextData, refresher, currentUser) => {
  let basePath
  if ('workspace' === contextType) {
    basePath = workspaceRoute(contextData, 'community')
  } else {
    basePath = toolRoute('community')
  }

  return {
    primaryAction: (group) => getDefaultAction(group, refresher, basePath, currentUser),
    actions: (groups) => getActions(groups, refresher, basePath, currentUser),
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
        options: {long: true},
        displayed: true,
        sortable: false
      }, {
        name: 'organizations',
        label: trans('organizations'),
        type: 'organizations',
        displayed: false,
        displayable: false,
        sortable: false
      }
    ],
    card: GroupCard
  }
}
