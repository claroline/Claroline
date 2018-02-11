import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {t} from '#/main/core/translation'
import {generateUrl} from '#/main/core/api/router'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {UserAvatar} from '#/main/core/user/components/avatar.jsx'
import {constants as listConst} from '#/main/core/data/list/constants'

import {actions} from '#/main/core/contact/tool/actions'
import {select} from '#/main/core/contact/tool/selectors'
import {OptionsType} from '#/main/core/contact/prop-types'

const VisibleUsersComponent = props =>
  <DataListContainer
    name="visibleUsers"
    open={{
      action: (row) => generateUrl('claro_user_profile', {'publicUrl': row.meta.publicUrl})
    }}
    fetch={{
      url: ['apiv2_visible_users_list'],
      autoload: true
    }}
    display={{
      current: listConst.DISPLAY_TILES_SM,
      available: Object.keys(listConst.DISPLAY_MODES)
    }}
    actions={[
      {
        icon: 'fa fa-fw fa-address-book-o',
        label: t('add_contact'),
        action: (rows) => props.createContacts(rows.map(r => r.id))
      }, {
        icon: 'fa fa-fw fa-paper-plane-o',
        label: t('send_message'),
        action: (rows) => {
          window.location = `${generateUrl('claro_message_show', {'message': 0})}?${rows.map(u => `userIds[]=${u.autoId}`).join('&')}`
        }
      }
    ]}
    definition={[
      {
        name: 'username',
        type: 'username',
        label: t('username'),
        displayed: props.options.data.show_username,
        primary: props.options.data.show_username
      },
      {
        name: 'lastName',
        type: 'string',
        label: t('last_name'),
        displayed: true,
        primary: !props.options.data.show_username
      },
      {
        name: 'firstName',
        type: 'string',
        label: t('first_name'),
        displayed: true
      },
      {
        name: 'email',
        type: 'string',
        label: t('email'),
        displayed: props.options.data.show_mail
      },
      {
        name: 'phone',
        type: 'string',
        label: t('phone'),
        displayed: props.options.data.show_phone
      }
    ]}
    card={(row) => ({
      icon: <UserAvatar picture={row.picture} alt={true} />,
      title: row.username,
      subtitle: row.firstName + ' ' + row.lastName
    })}
  />

VisibleUsersComponent.propTypes = {
  options: T.shape(OptionsType.propTypes),
  createContacts: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    options: select.options(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    createContacts: users => dispatch(actions.createContacts(users))
  }
}

const VisibleUsers = connect(mapStateToProps, mapDispatchToProps)(VisibleUsersComponent)

export {
  VisibleUsers
}
