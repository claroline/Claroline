import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {generateUrl} from '#/main/core/api/router'
import {DataListContainer} from '#/main/core/data/list/containers/data-list'
import {UserCard} from '#/main/core/user/data/components/user-card'
import {constants as listConst} from '#/main/core/data/list/constants'

import {select} from '#/main/core/user/contact/selectors'
import {OptionsType} from '#/main/core/user/contact/prop-types'

const ContactCard = props =>
  <UserCard
    {...omit(props, 'data')}
    {...props.data}
  />

ContactCard.propTypes = {
  data: T.object.isRequired
}

const ContactsComponent = props =>
  <DataListContainer
    name="contacts"
    display={{
      current: listConst.DISPLAY_TILES_SM,
      available: Object.keys(listConst.DISPLAY_MODES)
    }}
    fetch={{
      url: ['apiv2_contact_list'],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: 'url',
      target: ['claro_user_profile', {publicUrl: row.data.meta.publicUrl}]
    })}
    delete={{
      url: ['apiv2_contact_delete_bulk']
    }}
    actions={(rows) => [
      {
        type: 'url',
        icon: 'fa fa-fw fa-paper-plane-o',
        label: trans('send_message'),
        target: `${generateUrl('claro_message_show', {'message': 0})}?${rows.map(c => `userIds[]=${c.data.autoId}`).join('&')}`
      }
    ]}
    definition={[
      {
        name: 'data.username',
        type: 'username',
        alias: 'username',
        label: trans('username'),
        displayed: props.options.data.show_username,
        primary: props.options.data.show_username
      }, {
        name: 'data.lastName',
        type: 'string',
        alias: 'lastName',
        label: trans('last_name'),
        displayed: true,
        primary: !props.options.data.show_username
      }, {
        name: 'data.firstName',
        type: 'string',
        alias: 'firstName',
        label: trans('first_name'),
        displayed: true
      }, {
        name: 'data.email',
        type: 'string',
        alias: 'email',
        label: trans('email'),
        displayed: props.options.data.show_mail
      }, {
        name: 'data.phone',
        type: 'string',
        alias: 'phone',
        label: trans('phone'),
        displayed: props.options.data.show_phone
      }, {
        name: 'group',
        type: 'string',
        label: trans('group'),
        displayed: false,
        displayable: false,
        filterable: true
      }
    ]}
    card={ContactCard}
  />

ContactsComponent.propTypes = {
  options: T.shape(OptionsType.propTypes)
}

const Contacts = connect(
  (state) => ({
    options: select.options(state)
  })
)(ContactsComponent)

export {
  Contacts
}
