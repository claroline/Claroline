import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {MODAL_USERS} from '#/main/core/modals/users'
import {MODAL_MESSAGE} from '#/plugin/message/modals/message'

import {Contacts} from '#/plugin/message/tools/messaging/components/contacts'
import {ReceivedMessages} from '#/plugin/message/tools/messaging/components/received-messages'
import {SentMessages} from '#/plugin/message/tools/messaging/components/sent-messages'
import {DeletedMessages} from '#/plugin/message/tools/messaging/components/deleted-messages'
import {Message} from '#/plugin/message/tools/messaging/components/message'

const MessagingTool = (props) =>
  <ToolPage
    primaryAction="send add-contact"
    actions={[
      {
        name: 'send',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('send-message', {}, 'actions'),
        modal: [MODAL_MESSAGE],
        primary: true
      }, {
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
    subtitle={
      <Routes
        path={props.path}
        routes={[
          {path: '/received', render: () => trans('messages_received', {}, 'message')},
          {path: '/sent',     render: () => trans('messages_sent', {}, 'message')},
          {path: '/deleted',  render: () => trans('messages_removed', {}, 'message')},
          {path: '/contacts', render: () => trans('contacts', {}, 'message')}
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
          path: '/message/:id',
          onEnter: (params) => props.openMessage(params.id),
          render(routeProps) {
            const CurrentMessage = (
              <Message
                currentId={routeProps.match.params.id}
              />
            )

            return CurrentMessage
          }
        }
      ]}
    />
  </ToolPage>

MessagingTool.propTypes = {
  path: T.string.isRequired,

  addContacts: T.func.isRequired,
  openMessage: T.func.isRequired
}

export {
  MessagingTool
}
