import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {MODAL_USERS} from '#/main/core/modals/users'
import {MODAL_MESSAGING_PARAMETERS} from '#/plugin/message/tools/messaging/modals/parameters'

import {Contacts} from '#/plugin/message/tools/messaging/components/contacts'
import {ReceivedMessages} from '#/plugin/message/tools/messaging/components/received-messages'
import {SentMessages} from '#/plugin/message/tools/messaging/components/sent-messages'
import {DeletedMessages} from '#/plugin/message/tools/messaging/components/deleted-messages'
import {NewMessage} from '#/plugin/message/tools/messaging/components/new-message'
import {Message} from '#/plugin/message/tools/messaging/components/message'

const MessagingTool = (props) =>
  <ToolPage
    toolbar="send add-contact | more"
    actions={[
      {
        name: 'send',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-paper-plane',
        label: trans('send-message', {}, 'actions'),
        target: props.path + '/new',
        primary: true
      }, {
        name: 'add-contact',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-user-plus',
        label: trans('new-contact', {}, 'actions'),
        modal: [MODAL_USERS, {
          url: ['apiv2_visible_users_list'],
          selectAction: (users) => ({
            type: CALLBACK_BUTTON,
            label: trans('add-contact', {}, 'actions'),
            callback: () => props.addContacts(users.map(r => r.id))
          })
        }]
      }, {
        name: 'configure',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-cog',
        label: trans('configure', {}, 'actions'),
        modal: [MODAL_MESSAGING_PARAMETERS],
        group: trans('management')
      }
    ]}
    subtitle={
      <Routes
        path={props.path}
        routes={[
          {path: '/received', render: () => trans('messages_received', {}, 'message')},
          {path: '/sent',     render: () => trans('messages_sent', {}, 'message')},
          {path: '/deleted',  render: () => trans('messages_removed', {}, 'message')},
          {path: '/contacts', render: () => trans('contacts', {}, 'message')},
          {path: '/new',      render: () => trans('new_message', {}, 'message')}
        ]}
      />
    }
  >
    <Routes
      path={props.path}
      redirect={[
        {from: '/', exact: true, to: '/received' }
      ]}
      routes={[
        {
          path: '/contacts',
          component: Contacts
        }, {
          path: '/received',
          component: ReceivedMessages
        }, {
          path: '/sent',
          component: SentMessages
        }, {
          path: '/deleted',
          component: DeletedMessages
        }, {
          path: '/new',
          component: NewMessage,
          onEnter: () => props.newMessage()
        }, {
          path: '/message/:id?',
          component: Message,
          onEnter: (params) => {
            props.openMessage(params.id)
            props.newMessage(params.id)
            props.setAsReply()
          }
        }
      ]}
    />
  </ToolPage>

MessagingTool.propTypes = {
  path: T.string.isRequired,

  addContacts: T.func.isRequired,
  openMessage: T.func.isRequired,
  newMessage: T.func.isRequired,
  setAsReply: T.func.isRequired
}

export {
  MessagingTool
}
