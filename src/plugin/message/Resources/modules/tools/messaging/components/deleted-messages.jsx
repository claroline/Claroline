import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions, selectors} from '#/plugin/message/tools/messaging/store'
import {MessageCard} from '#/plugin/message/data/components/message-card'

const DeletedMessagesComponent = (props) =>
  <ListData
    name={`${selectors.STORE_NAME}.deletedMessages`}
    fetch={{
      url: ['apiv2_message_removed'],
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
        sortable: true
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
        icon: 'fa fa-fw fa-sync-alt',
        label: trans('restore', {}, 'actions'),
        callback: () => props.restoreMessages(rows),
        confirm: {
          title: trans('messages_restore_title', {}, 'message'),
          message: trans('messages_restore_confirm', {}, 'message')
        }
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-trash',
        label: trans('delete', {}, 'actions'),
        dangerous: true,
        confirm: {
          title: trans('messages_delete_title', {}, 'message'),
          message: trans('messages_delete_confirm_permanent', {}, 'message')
        },
        callback: () => props.deleteMessages(rows)
      }
    ]}
    card={MessageCard}
  />

DeletedMessagesComponent.propTypes = {
  path: T.string.isRequired,
  deleteMessages: T.func.isRequired,
  restoreMessages: T.func.isRequired
}

const DeletedMessages = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    deleteMessages(messages) {
      dispatch(actions.deleteMessages(messages))
    },
    restoreMessages(messages) {
      dispatch(actions.restoreMessages(messages))
    }
  })
)(DeletedMessagesComponent)

export {
  DeletedMessages
}
