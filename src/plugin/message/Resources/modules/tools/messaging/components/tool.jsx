import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Tool} from '#/main/core/tool'

import {Contacts} from '#/plugin/message/tools/messaging/components/contacts'
import {MessageInbox} from '#/plugin/message/tools/messaging/components/inbox'
import {MessageSent} from '#/plugin/message/tools/messaging/components/sent'
import {MessageTrash} from '#/plugin/message/tools/messaging/components/trash'
import {Message} from '#/plugin/message/tools/messaging/components/message'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

const MessagingTool = (props) =>
  <Tool
    {...props}
    menu={[
      {
        name: 'inbox',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-inbox',
        label: trans('messages_received', {}, 'message'),
        target: props.path,
        exact: true
      }, {
        name: 'sent',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-paper-plane',
        label: trans('messages_sent', {}, 'message'),
        target: props.path+'/sent'
      }, {
        name: 'deleted',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-trash',
        label: trans('messages_removed', {}, 'message'),
        target: props.path+'/deleted'
      }, {
        name: 'contact',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-address-book',
        label: trans('contacts', {}, 'message'),
        target: props.path+'/contacts'
      }
    ]}
    pages={[
      {
        path: '/',
        component: MessageInbox,
        exact: true
      }, {
        path: '/sent',
        component: MessageSent
      }, {
        path: '/deleted',
        component: MessageTrash
      }, {
        path: '/contacts',
        component: Contacts
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

MessagingTool.propTypes = {
  openMessage: T.func.isRequired
}

export {
  MessagingTool
}
