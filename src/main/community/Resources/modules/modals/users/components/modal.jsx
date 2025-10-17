import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {PickerModal} from '#/main/app/data/modals/picker/components/modal'

import {UserAvatar} from '#/main/app/user/components/avatar'
import {constants} from '#/main/app/user/constants'
import {UserStatus} from '#/main/app/user/components/status'
import {UserCard} from '#/main/community/user/components/card'

const UsersModal = (props) =>
  <PickerModal
    icon="fa fa-fw fa-user"
    title={trans('users', {}, 'community')}
    url={['apiv2_user_list']}
    {...props}
    name="usersPicker"
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
        filterable: false
      }, {
        name: 'lastName',
        type: 'string',
        label: trans('last_name'),
        displayed: true,
        primary: !param('community.username'),
        filterable: false
      }, {
        name: 'email',
        type: 'email',
        label: trans('email'),
        displayable: true,
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
        alias: 'created',
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
    ]}
    card={UserCard}
  />

UsersModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  selectAction: T.func.isRequired,
  multiple: T.bool
}

export {
  UsersModal
}
