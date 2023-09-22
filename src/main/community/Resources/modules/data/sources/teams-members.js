import React from 'react'

import {trans} from '#/main/app/intl/translation'

import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {UserAvatar} from '#/main/core/user/components/avatar'
import {UserCard} from '#/main/community/user/components/card'
import {getActions, getDefaultAction} from '#/main/community/user/utils'

export default (contextType, contextData, refresher, currentUser) => {
  let basePath
  if ('workspace' === contextType) {
    basePath = workspaceRoute(contextData, 'community')
  } else {
    basePath = toolRoute('community')
  }

  return {
    primaryAction: (user) => getDefaultAction(user, refresher, basePath, currentUser),
    actions: (users) => getActions(users, refresher, basePath, currentUser),
    definition: [
      {
        name: 'picture',
        type: 'user',
        label: trans('avatar'),
        displayed: true,
        filterable: false,
        sortable: false,
        render: (user) => (
          <UserAvatar picture={user.picture} alt={false} />
        )
      }, {
        name: 'username',
        type: 'username',
        label: trans('username'),
        displayed: true,
        primary: true
      }, {
        name: 'lastName',
        type: 'string',
        label: trans('last_name'),
        displayed: true
      }, {
        name: 'firstName',
        type: 'string',
        label: trans('first_name'),
        displayed: true
      }, {
        name: 'email',
        type: 'email',
        label: trans('email'),
        displayed: true
      }, {
        name: 'administrativeCode',
        type: 'string',
        label: trans('code')
      }, {
        name: 'meta.personalWorkspace',
        alias: 'hasPersonalWorkspace',
        type: 'boolean',
        label: trans('has_personal_workspace')
      }, {
        name: 'restrictions.disabled',
        alias: 'isDisabled',
        type: 'boolean',
        label: trans('disabled'),
        displayed: true
      }, {
        name: 'meta.created',
        type: 'date',
        alias: 'created',
        label: trans('creation_date')
      }, {
        name: 'meta.lastActivity',
        type: 'date',
        alias: 'lastActivity',
        label: trans('last_activity'),
        displayed: true,
        options: {
          time: true
        }
      }, {
        name: 'team',
        type: 'team',
        label: trans('team', {}, 'data_sources'),
        displayed: true,
        filterable: true
      }
    ],
    card: UserCard
  }
}
