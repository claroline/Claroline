import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {route} from '#/main/core/user/routing'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {UserCard} from '#/main/core/user/components/card'

const UserList = props =>
  <ListData
    {...omit(props, 'url', 'customDefinition')}
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
        render: (user) => (
          <UserAvatar picture={user.picture} alt={false} />
        )
      }, {
        name: 'username',
        type: 'username',
        label: trans('username'),
        displayable: param('community.username'),
        displayed: param('community.username'),
        sortable: param('community.username'),
        filterable: param('community.username'),
        primary: param('community.username')
      }, {
        name: 'lastName',
        type: 'string',
        label: trans('last_name'),
        displayed: true,
        primary: !param('community.username')
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
        name: 'meta.created',
        type: 'date',
        alias: 'created',
        label: trans('creation_date'),
        filterable: false
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
        name: 'groups',
        type: 'groups',
        label: trans('groups'),
        displayed: false,
        sortable: false
      }, {
        name: 'organizations',
        label: trans('organizations'),
        type: 'organizations',
        displayed: false,
        displayable: false,
        sortable: false
      }, {
        name: 'restrictions.disabled',
        alias: 'isDisabled',
        type: 'boolean',
        label: trans('disabled'),
        displayable: false,
        sortable: false,
        filterable: true
      }
    ].concat(props.customDefinition)}
    card={UserCard}
  />

UserList.propTypes = {
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  primaryAction: T.func,
  actions: T.func,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  }))
}

UserList.defaultProps = {
  customDefinition: []
}

export {
  UserList
}
