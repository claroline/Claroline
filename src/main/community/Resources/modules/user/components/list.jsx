import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {ListData} from '#/main/app/content/list/containers/data'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {constants} from '#/main/app/user/constants'
import {getActions, getDefaultAction} from '#/main/community/user/utils'
import {UserAvatar} from '#/main/app/user/components/avatar'
import {UserCard} from '#/main/community/user/components/card'
import {UserStatus} from '#/main/app/user/components/status'

const UserList = (props) => {
  const dispatch = useDispatch()
  const currentUser = useSelector(securitySelectors.currentUser)

  const usersRefresher = {
    add:    () => dispatch(listActions.invalidateData(props.name)),
    update: () => dispatch(listActions.invalidateData(props.name)),
    delete: () => dispatch(listActions.invalidateData(props.name))
  }

  return (
    <ListData
      primaryAction={(row) => getDefaultAction(row, usersRefresher, props.path, currentUser)}
      actions={(rows) => getActions(rows, usersRefresher, props.path, currentUser).then((actions) => [].concat(actions, props.customActions(rows)))}
      definition={[
        {
          name: 'username',
          type: 'string',
          label: trans('username'),
          displayable: param('community.username'),
          displayed: param('community.username'),
          sortable: param('community.username'),
          filterable: false,
          primary: param('community.username'),
          render: (user) => (
            <div className="d-flex flex-direction-row gap-3 align-items-center" role="presentation">
              <UserAvatar user={user} size="xs" />
              {user.username}
            </div>
          )
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
          name: 'firstName',
          type: 'string',
          label: trans('first_name'),
          displayed: true,
          filterable: true
        }, {
          name: 'lastName',
          type: 'string',
          label: trans('last_name'),
          displayed: true,
          primary: !param('community.username'),
          filterable: true
        }, {
          name: 'email',
          type: 'email',
          label: trans('email'),
          displayable: true,
          filterable: false
        }, {
          name: 'administrativeCode',
          type: 'string',
          label: trans('code'),
          filterable: false
        }, {
          name: 'lastActivity',
          type: 'date',
          label: trans('last_activity'),
          displayed: true,
          options: {
            time: true
          }
        }, {
          name: 'meta.created',
          type: 'date',
          alias: 'createdAt',
          label: trans('creation_date'),
          filterable: true
        }, {
          name: 'restrictions.disabled',
          alias: 'disabled',
          type: 'boolean',
          label: trans('disabled'),
          displayable: false,
          sortable: false,
          filterable: true
        }
      ].concat(props.customDefinition)}

      {...omit(props, 'path', 'url', 'autoload', 'customDefinition', 'customActions', 'invalidate')}

      name={props.name}
      fetch={{
        url: props.url,
        autoload: props.autoload
      }}
      card={UserCard}
    />
  )
}

UserList.propTypes = {
  path: T.string,
  currentUser: T.object,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  autoload: T.bool,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  })),
  customActions: T.func
}

UserList.defaultProps = {
  autoload: true,
  customDefinition: [],
  customActions: () => []
}

export {
  UserList
}
