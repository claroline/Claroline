import React from 'react'

import {trans} from '#/main/app/intl/translation'

import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'

import {UserAvatar} from '#/main/app/user/components/avatar'
import {UserCard} from '#/main/community/user/components/card'
import {getActions, getDefaultAction} from '#/main/community/user/utils'
import {constants} from '#/main/app/user/constants'
import {UserStatus} from '#/main/app/user/components/status'

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
        name: 'username',
        type: 'username',
        label: trans('username'),
        displayed: true,
        primary: true,
        render: (user) => (
          <div className="d-flex flex-direction-row gap-3 align-items-center">
            <UserAvatar user={user} size="xs" />
            {user.username}
          </div>
        )
      }, {
        name: 'firstName',
        type: 'string',
        label: trans('first_name'),
        displayed: true
      }, {
        name: 'lastName',
        type: 'string',
        label: trans('last_name'),
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
        name: 'status',
        type: 'choice',
        label: trans('status'),
        displayable: true,
        filterable: true,
        sortable: false,
        options: {
          choices: constants.USER_STATUSES
        },
        render: (user) => <UserStatus user={user} variant="badge" />
      }, {
        name: 'lastActivity',
        type: 'date',
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
