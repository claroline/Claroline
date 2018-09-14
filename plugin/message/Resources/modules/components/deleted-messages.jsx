import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {actions as modalActions} from '#/main/app/overlay/modal/store'

import {actions} from '#/plugin/message/actions'
import {MessageCard} from '#/plugin/message/data/components/message-card'

const DeletedMessagesComponent = (props) =>
  <div>
    <h2>{trans('messages_removed')}</h2>
    <ListData
      name="deletedMessages"
      fetch={{
        url: ['apiv2_message_removed'],
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
          label: trans('message_form_object'),
          displayed: true,
          primary: true
        }, {
          name: 'from.username',
          alias: 'senderUsername',
          type: 'string',
          label: trans('from_message'),
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
        }, {
          name: 'meta.read',
          alias: 'isRead',
          type: 'boolean',
          label: trans('message_read', {}, 'message'),
          displayed: true,
          searchable: true,
          filterable: true
        }
      ]}
      actions={(rows) => [
        {
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-eye',
          label: trans('see_message', {}, 'message'),
          target: '/message/'+rows[0].id,
          scope: ['object']
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-sync-alt',
          label: trans('restore'),
          callback: () => props.restoreMessages(rows)
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash-o',
          label: trans('delete'),
          dangerous: true,
          callback: () => props.deleteMessages(rows)
        }
      ]}
      card={(props) =>
        <MessageCard
          {...props}
          contentText={props.data.content}
        />
      }
    />
  </div>

DeletedMessagesComponent.propTypes = {
  deleteMessages: T.func.isRequired,
  restoreMessages: T.func.isRequired,
  data: T.shape({
    content: T.string
  })
}

const DeletedMessages = connect(
  null,
  dispatch => ({
    deleteMessages(messages) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          title: trans('messages_delete_title'),
          question: trans('messages_confirm_permanent_delete'),
          dangerous: true,
          handleConfirm: () => dispatch(actions.deleteMessages(messages))
        })
      )
    },
    restoreMessages(messages) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          title: trans('messages_restore_title'),
          question: trans('messages_confirm_restore'),
          handleConfirm: () => dispatch(actions.restoreMessages(messages))
        })
      )
    }
  })
)(DeletedMessagesComponent)

export {
  DeletedMessages
}
