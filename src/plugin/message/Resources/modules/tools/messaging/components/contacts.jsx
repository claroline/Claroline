import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'
import {ToolPage} from '#/main/core/tool'

import {route} from '#/main/community/user/routing'
import {ContactCard} from '#/plugin/message/data/components/contact-card'
import {ToolParameters as ToolParametersTypes} from '#/plugin/message/tools/messaging/prop-types'
import {actions, selectors} from '#/plugin/message/tools/messaging/store'
import {MODAL_USERS} from '#/main/community/modals/users'

const ContactsComponent = props =>
  <ToolPage
    title={trans('contacts', {}, 'message')}
    primaryAction="add-contact"
    actions={[
      {
        name: 'add-contact',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-user-plus',
        label: trans('new-contact', {}, 'actions'),
        modal: [MODAL_USERS, {
          selectAction: (users) => ({
            type: CALLBACK_BUTTON,
            label: trans('add-contact', {}, 'actions'),
            callback: () => props.addContacts(users.map(r => r.id))
          })
        }]
      }
    ]}
  >
    <ListData
      name={`${selectors.STORE_NAME}.contacts`}
      display={{
        current: listConst.DISPLAY_TILES_SM,
        available: listConst.DISPLAY_MODES
      }}
      fetch={{
        url: ['apiv2_contact_list'],
        autoload: true
      }}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        target: route(row.data)
      })}
      delete={{
        url: ['apiv2_contact_delete_bulk']
      }}
      definition={[
        {
          name: 'data.username',
          type: 'username',
          alias: 'username',
          label: trans('username'),
          primary: true
        }, {
          name: 'data.lastName',
          type: 'string',
          alias: 'lastName',
          label: trans('last_name'),
          displayed: true
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
          displayed: props.options.show_mail
        }, {
          name: 'data.phone',
          type: 'string',
          alias: 'phone',
          label: trans('phone'),
          displayed: props.options.show_phone
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
  </ToolPage>

ContactsComponent.propTypes = {
  options: T.shape(
    ToolParametersTypes.propTypes
  ),
  addContacts: T.func.isRequired
}

const Contacts = connect(
  () => ({
    options: {
      show_mail: true,
      show_phone: true
    }
  }),
  (dispatch) => ({
    addContacts(users) {
      dispatch(actions.addContacts(users))
    }
  })
)(ContactsComponent)

export {
  Contacts
}
