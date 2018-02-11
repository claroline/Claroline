import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {t} from '#/main/core/translation'
import {generateUrl} from '#/main/core/api/router'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {UserAvatar} from '#/main/core/user/components/avatar.jsx'
import {constants as listConst} from '#/main/core/data/list/constants'

import {select} from '#/main/core/contact/tool/selectors'
import {OptionsType} from '#/main/core/contact/prop-types'

const ContactsComponent = props =>
  <DataListContainer
    name="contacts"
    open={{
      action: (row) => generateUrl('claro_user_profile', {'publicUrl': row.data.meta.publicUrl})
    }}
    display={{
      current: listConst.DISPLAY_TILES_SM,
      available: Object.keys(listConst.DISPLAY_MODES)
    }}
    fetch={{
      url: ['apiv2_contact_list'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_contact_delete_bulk']
    }}
    actions={[
      {
        icon: 'fa fa-fw fa-paper-plane-o',
        label: t('send_message'),
        action: (rows) => {
          window.location = `${generateUrl('claro_message_show', {'message': 0})}?${rows.map(c => `userIds[]=${c.data.autoId}`).join('&')}`
        }
      }
    ]}
    definition={[
      {
        name: 'data.username',
        type: 'username',
        label: t('username'),
        displayed: props.options.data.show_username,
        primary: props.options.data.show_username
      }, {
        name: 'data.lastName',
        type: 'string',
        label: t('last_name'),
        displayed: true,
        primary: !props.options.data.show_username
      }, {
        name: 'data.firstName',
        type: 'string',
        label: t('first_name'),
        displayed: true
      }, {
        name: 'data.email',
        type: 'string',
        label: t('email'),
        displayed: props.options.data.show_mail
      }, {
        name: 'data.phone',
        type: 'string',
        label: t('phone'),
        displayed: props.options.data.show_phone
      }
    ]}
    card={row => ({
      icon: <UserAvatar picture={row.data.picture} alt={true}/>,
      title: row.data.username,
      subtitle: row.data.firstName + ' ' + row.data.lastName
    })}
  />

ContactsComponent.propTypes = {
  options: T.shape(OptionsType.propTypes)
}

function mapStateToProps(state) {
  return {
    options: select.options(state)
  }
}

const Contacts = connect(mapStateToProps, {})(ContactsComponent)

export {
  Contacts
}
