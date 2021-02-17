import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {UserCard} from '#/main/core/user/components/card'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {User as UserType} from '#/main/core/user/prop-types'

import {selectors} from '#/main/core/modals/users/store'

const UsersModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'url', 'selected', 'selectAction', 'reset')}
      icon="fa fa-fw fa-user"
      className="data-picker-modal"
      bsSize="lg"
      onExited={props.reset}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: props.url,
          autoload: true
        }}
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
            type: 'email',
            label: trans('email'),
            displayed: true
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
          }, {
            name: 'emails',
            label: trans('emails'),
            type: 'string',
            displayed: false,
            displayable: false,
            sortable: false
          }
        ]}
        card={UserCard}
      />

      <Button
        label={trans('select', {}, 'actions')}
        {...selectAction}
        className="modal-btn btn"
        primary={true}
        disabled={0 === props.selected.length}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

UsersModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  subtitle: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.arrayOf(T.shape(UserType.propTypes)).isRequired,
  reset: T.func.isRequired
}

UsersModal.defaultProps = {
  url: ['apiv2_user_list'],
  title: trans('users')
}

export {
  UsersModal
}
