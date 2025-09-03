import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch} from 'react-redux'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {param} from '#/main/app/config'
import {makeId} from '#/main/app/utils/id'
import {API_REQUEST} from '#/main/app/api'
import {PickerMultipleModal} from '#/main/app/data/modals/picker/components/multiple-modal'

import {constants as userConstants} from '#/main/app/user/constants'
import {constants} from '#/main/community/constants'
import {UserCard} from '#/main/community/user/components/card'
import {UserAvatar} from '#/main/app/user/components/avatar'
import {UserStatus} from '#/main/app/user/components/status'
import {RoleCard} from '#/main/community/role/components/card'

const RolesModal = (props) => {
  const dispatch = useDispatch()

  return (
    <PickerMultipleModal
      {...omit(props, 'personal', 'contextId')}
      icon="fa fa-fw fa-id-badge"
      tabs={[
        {
          name: 'rolePicker',
          title: props.contextId ? trans('workspace') : trans('platform'),
          url: props.url,
          definition: [
            {
              name: 'translationKey',
              type: 'translation',
              label: trans('name'),
              displayed: true,
              primary: true
            }, {
              name: 'name',
              type: 'string',
              label: trans('code'),
              displayed: false
            }, {
              name: 'meta.description',
              type: 'string',
              label: trans('description'),
              options: {long: true},
              displayed: true,
              sortable: false
            }
          ],
          card: RoleCard
        }
      ].concat(props.personal ? {
        name: 'rolePersonalPicker',
        url: ['apiv2_user_list', {contextId: props.contextId}],
        title: trans('user'),
        card: UserCard,
        definition: [
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
              choices: userConstants.USER_STATUSES
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
        ]
      } : [])}
      selectAction={(selected) => {
        const missingRoles = []
        const selectedRoles = []

        selected.map(s => {
          if (s.username || s.lastName) {
            const userRole = s.roles.find(r => constants.ROLE_USER === r.type)
            if (userRole) {
              selectedRoles.push(userRole)
            } else {
              const missingRole = {
                id: makeId(),
                name: 'ROLE_USER_'+s.username.toUpperCase(),
                type: constants.ROLE_USER,
                translationKey: s.username,
                meta: {readOnly: true},
                user: s
              }
              selectedRoles.push(missingRole)
              missingRoles.push(missingRole)
            }
          } else {
            selectedRoles.push(s)
          }
        })

        return Object.assign({}, props.selectAction(selectedRoles), {
          onClick: () => {
            if (!isEmpty(missingRoles)) {
              return dispatch({
                [API_REQUEST]: {
                  url: ['apiv2_role_create_user_roles'],
                  request: {
                    method: 'POST',
                    body: JSON.stringify(missingRoles)
                  },
                  success: () => props.fadeModal()
                }
              })
            }

            props.fadeModal()
          }
        })
      }}
    />
  )
}

RolesModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  selectAction: T.func.isRequired,
  multiple: T.bool,
  contextId: T.string,
  personal: T.bool,
  fadeModal: T.func.isRequired
}

RolesModal.defaultProps = {
  url: ['apiv2_role_list'],
  title: trans('roles', {}, 'community'),
  personal: true
}

export {
  RolesModal
}
