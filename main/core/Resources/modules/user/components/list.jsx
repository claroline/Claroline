import React from 'react'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {actions as listActions} from '#/main/app/content/list/store'

import {route} from '#/main/core/user/routing'
import {UserCard} from '#/main/core/user/components/card'

const Users = props =>
  <ListData
    name={props.name}
    fetch={{
      url: props.url,
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: route(row)
    })}
    actions={(rows) => props.getActions ? props.getActions(rows): []}
    definition={[
      {
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
        name: 'meta.lastLogin',
        type: 'date',
        alias: 'lastLogin',
        label: trans('last_login'),
        displayed: true,
        options: {
          time: true
        }
      }
    ]}
    card={UserCard}
  />

const UserList = connect(
  null,
  dispatch => ({
    invalidate(name) {
      dispatch(listActions.invalidateData(name))
    }
  })
)(Users)

export {
  UserList
}
