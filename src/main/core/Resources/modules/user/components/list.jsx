import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {route} from '#/main/core/user/routing'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {UserCard} from '#/main/core/user/components/card'

const UserList = props =>
  <ListData
    name={props.name}
    fetch={{
      url: props.url,
      autoload: true
    }}
    primaryAction={props.primaryAction || ((row) => ({
      type: LINK_BUTTON,
      target: route(row)
    }))}
    actions={props.actions}
    definition={[
      {
        name: 'picture',
        type: 'user', // required to get correct styles (no padding + small picture size)
        label: trans('avatar'),
        displayed: true,
        filterable: false,
        sortable: false,
        render: (user) => {
          const Avatar = (
            <UserAvatar picture={user.picture} alt={false} />
          )

          return Avatar
        }
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
        alias: 'mail',
        type: 'email',
        label: trans('email'),
        displayed: true
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
        name: 'group_name',
        type: 'string',
        label: trans('group'),
        displayed: false,
        displayable: false,
        sortable: false
      }, {
        name: 'unionOrganizationName',
        label: trans('organization'),
        type: 'string',
        displayed: false,
        displayable: false,
        sortable: false
      }
    ]}
    card={UserCard}
  />

UserList.propTypes = {
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  primaryAction: T.func,
  actions: T.func
}

export {
  UserList
}
