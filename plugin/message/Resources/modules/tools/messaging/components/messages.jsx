import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {actions as listActions} from '#/main/app/content/list/store'
import {actions as formActions} from '#/main/app/content/form/store'

import {ReceivedMessages} from '#/plugin/message/tools/messaging/components/received-messages'
import {SentMessages} from '#/plugin/message/tools/messaging/components/sent-messages'
import {DeletedMessages} from '#/plugin/message/tools/messaging/components/deleted-messages'
import {NewMessage} from '#/plugin/message/tools/messaging/components/new-message'
import {MessagesParameters} from '#/plugin/message/tools/messaging/components/messages-parameters'
import {Message} from '#/plugin/message/tools/messaging/components/message'
import {actions, selectors} from '#/plugin/message/tools/messaging/store'


const MessagesComponent = (props) =>
  <Routes
    redirect={[
      {from: '/', exact: true, to: '/received' }
    ]}
    routes={[
      {
        path: '/received',
        exact: true,
        component: ReceivedMessages,
        onEnter: () => props.invalidateData('receivedMessages')
      }, {
        path: '/sent',
        exact: true,
        component: SentMessages,
        onEnter: () => props.invalidateData('sentMessages')
      }, {
        path: '/deleted',
        exact: true,
        component: DeletedMessages,
        onEnter: () => props.invalidateData('deletedMessages')
      }, {
        path: '/new',
        exact: true,
        component: NewMessage,
        onEnter: () => props.newMessage()
      }, {
        path: '/message/:id?',
        exact: true,
        component: Message,
        onEnter: (params) => {
          props.openMessage(params.id)
          props.newMessage(params.id)
          props.setAsReply()
        }
      }, {
        path: '/parameters',
        exact: true,
        component: MessagesParameters,
        onEnter: () => props.resetForm(props.mailNotified)
      }
    ]}
  />

MessagesComponent.propTypes = {
  invalidateData: T.func.isRequired,
  openMessage: T.func,
  newMessage: T.func,
  setAsReply: T.func,
  resetForm: T.func,
  mailNotified: T.bool.isRequired
}

const Messages = connect(
  state => ({
    mailNotified: selectors.mailNotified(state)
  }),
  dispatch => ({
    openMessage(id) {
      dispatch(actions.openMessage(id))
    },
    newMessage(id) {
      dispatch(actions.newMessage(id))
    },
    setAsReply() {
      dispatch(actions.setAsReply())
    },
    invalidateData(form) {
      dispatch(listActions.invalidateData(form))
    },
    resetForm(mailNotified) {
      dispatch(formActions.resetForm('messagesParameters', {mailNotified: mailNotified}))
    }
  })
)(MessagesComponent)


export {
  Messages
}
