import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {t} from '#/main/core/translation'
import {generateUrl} from '#/main/core/api/router'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {UserCard} from '#/main/core/user/data/components/user-card'
import {constants as listConst} from '#/main/core/data/list/constants'

import {actions} from '#/main/core/user/contact/actions'
import {select} from '#/main/core/user/contact/selectors'
import {OptionsType} from '#/main/core/user/contact/prop-types'

const VisibleUsersComponent = props =>
  <DataListContainer
    name="visibleUsers"
    primaryAction={(row) => ({
      type: 'url',
      target: ['claro_user_profile', {'publicUrl': row.meta.publicUrl}]
    })}
    fetch={{
      url: ['apiv2_visible_users_list'],
      autoload: true
    }}
    display={{
      current: listConst.DISPLAY_TILES_SM,
      available: Object.keys(listConst.DISPLAY_MODES)
    }}
    actions={(rows) => [
      {
        type: 'callback',
        icon: 'fa fa-fw fa-address-book-o',
        label: t('add_contact'),
        callback: () => props.createContacts(rows.map(r => r.id))
      }, {
        type: 'url',
        icon: 'fa fa-fw fa-paper-plane-o',
        label: t('send_message'),
        target: `${generateUrl('claro_message_show', {'message': 0})}?${rows.map(u => `userIds[]=${u.autoId}`).join('&')}`
      }
    ]}
    definition={[
      {
        name: 'username',
        type: 'username',
        label: t('username'),
        displayed: props.options.data.show_username,
        primary: props.options.data.show_username
      }, {
        name: 'lastName',
        type: 'string',
        label: t('last_name'),
        displayed: true,
        primary: !props.options.data.show_username
      }, {
        name: 'firstName',
        type: 'string',
        label: t('first_name'),
        displayed: true
      }, {
        name: 'email',
        type: 'string',
        label: t('email'),
        displayed: props.options.data.show_mail
      }, {
        name: 'phone',
        type: 'string',
        label: t('phone'),
        displayed: props.options.data.show_phone
      }
    ]}
    card={UserCard}
  />

VisibleUsersComponent.propTypes = {
  options: T.shape(OptionsType.propTypes),
  createContacts: T.func.isRequired
}

const VisibleUsers = connect(
  (state) => ({
    options: select.options(state)
  }),
  (dispatch) => ({
    createContacts: users => dispatch(actions.createContacts(users))
  })
)(VisibleUsersComponent)

export {
  VisibleUsers
}
