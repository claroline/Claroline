import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {MessageCard} from '#/plugin/message/data/components/message-card'
import {actions, selectors} from '#/plugin/message/tools/messaging/store'

const ReceivedMessagesComponent = (props) =>
  <ListData
    name={`${selectors.STORE_NAME}.receivedMessages`}
    fetch={{
      url: ['apiv2_message_received'],
      autoload: true
    }}
    primaryAction={(message) => ({
      type: LINK_BUTTON,
      target: props.path+'/message/'+message.id,
      label: trans('open', {}, 'actions')
    })}
    definition={[
      {
        name: 'object',
        type: 'string',
        label: trans('message_form_object', {}, 'message'),
        displayed: true,
        primary: true
      }, {
        name: 'content',
        type: 'html',
        label: trans('message'),
        displayed: true
      }, {
        name: 'from',
        type: 'user',
        label: trans('message_from', {}, 'message'),
        displayed: true,
        filterable: false,
        sortable: false
      }, {
        name: 'meta.date',
        alias: 'date',
        type: 'date',
        label: trans('date'),
        displayed: true,
        options: {
          time: true
        }
      }, {
        name: 'meta.read',
        alias: 'isRead',
        type: 'boolean',
        label: trans('message_read', {}, 'message'),
        displayed: true
      }
    ]}
    actions={(rows) => [
      {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-check',
        label: trans('marked_read_message', {}, 'message'),
        displayed: -1 !== rows.findIndex(message => !message.meta.read),
        callback: () => props.readMessages(rows)
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-times',
        label: trans('marked_unread_message', {}, 'message'),
        displayed: -1 !== rows.findIndex(message => message.meta.read),
        callback: () => props.unreadMessages(rows)
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-trash',
        label: trans('delete'),
        dangerous: true,
        confirm: {
          title: trans('messages_delete_title', {}, 'message'),
          message: trans('messages_delete_confirm', {}, 'message')
        },
        callback: () => props.removeMessages(rows)
      }
    ]}
    card={MessageCard}
  />

ReceivedMessagesComponent.propTypes = {
  path: T.string.isRequired,
  removeMessages: T.func.isRequired,
  unreadMessages: T.func.isRequired,
  readMessages: T.func.isRequired
}

const ReceivedMessages = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    removeMessages(message) {
      dispatch(actions.removeMessages(message, `${selectors.STORE_NAME}.receivedMessages`))
    },
    readMessages(messages) {
      dispatch(actions.readMessages(messages))
    },
    unreadMessages(messages) {
      dispatch(actions.unreadMessages(messages))
    }
  })
)(ReceivedMessagesComponent)

export {
  ReceivedMessages
}
