import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {actions as modalActions} from '#/main/app/overlay/modal/store'

import {MessageCard} from '#/plugin/message/data/components/message-card'
import {actions} from '#/plugin/message/tools/messaging/store'

const SentMessagesComponent = (props) =>
  <ListData
    title={trans('messages_sent', {}, 'message')}
    name="sentMessages"
    fetch={{
      url: ['apiv2_message_sent'],
      autoload: true
    }}
    primaryAction={(message) => ({
      type: LINK_BUTTON,
      target: '/message/'+message.id,
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
        name: 'to',
        type: 'string',
        label: trans('to_message', {}, 'message'),
        displayed: true,
        filterable: false,
        sortable: true
      }, {
        name: 'meta.date',
        alias: 'date',
        type: 'date',
        label: trans('date'),
        displayed: true,
        searchable: true,
        filterable: true,
        option: {
          time: true
        }
      }
    ]}
    actions={(rows) => [
      {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-trash-o',
        label: trans('delete', {}, 'actions'),
        dangerous: true,
        callback: () => props.removeMessages(rows, 'sentMessages'),
        confirm: {
          title: trans('messages_delete_title', {}, 'message'),
          message: trans('messages_delete_confirm', {}, 'message')
        }
      }
    ]}
    card={(props) =>
      <MessageCard
        {...props}
        contentText={props.data.content}
      />
    }
  />

SentMessagesComponent.propTypes = {
  removeMessages: T.func.isRequired,
  data: T.shape({
    content: T.string
  })
}

const SentMessages = connect(
  null,
  dispatch => ({
    removeMessages(message, form) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          title: trans('messages_delete_title', {}, 'message'),
          question: trans('remove_message_confirm_message', {}, 'message'),
          dangerous: true,
          handleConfirm: () => dispatch(actions.removeMessages(message, form))
        })
      )
    }
  })
)(SentMessagesComponent)


export {
  SentMessages
}
