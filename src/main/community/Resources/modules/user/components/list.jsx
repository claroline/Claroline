import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {ListData} from '#/main/app/content/list/containers/data'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {getActions, getDefaultAction} from '#/main/community/user/utils'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {UserCard} from '#/main/community/user/components/card'

const Users = (props) => {
  const usersRefresher = merge({
    add:    () => props.invalidate(props.name),
    update: () => props.invalidate(props.name),
    delete: () => props.invalidate(props.name)
  }, props.refresher || {})

  return (
    <ListData
      primaryAction={(row) => getDefaultAction(row, usersRefresher, props.path, props.currentUser)}
      actions={(rows) => getActions(rows, usersRefresher, props.path, props.currentUser).then((actions) => [].concat(actions, props.customActions(rows)))}
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

      {...omit(props, 'path', 'url', 'autoload', 'customDefinition', 'customActions', 'refresher', 'invalidate')}

      name={props.name}
      fetch={{
        url: props.url,
        autoload: props.autoload
      }}
      card={UserCard}
    />
  )
}

Users.propTypes = {
  path: T.string,
  currentUser: T.object,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  autoload: T.bool,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  })),
  customActions: T.func,
  invalidate: T.func.isRequired,
  refresher: T.shape({
    add: T.func,
    update: T.func,
    delete: T.func
  })
}

Users.defaultProps = {
  autoload: true,
  customDefinition: [],
  customActions: () => []
}

const UserList = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  }),
  dispatch => ({
    invalidate(name) {
      dispatch(listActions.invalidateData(name))
    }
  })
)(Users)

export {
  UserList
}
