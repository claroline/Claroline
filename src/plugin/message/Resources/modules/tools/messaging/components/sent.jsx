import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {MessageCard} from '#/plugin/message/data/components/message-card'
import {actions, selectors} from '#/plugin/message/tools/messaging/store'
import {ToolPage} from '#/main/core/tool'
import {MODAL_MESSAGE} from '#/plugin/message/modals/message'

const MessageSentComponent = (props) =>
  <ToolPage
    title={trans('messages_sent', {}, 'message')}
    primaryAction="send"
    actions={[
      {
        name: 'send',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('send-message', {}, 'actions'),
        modal: [MODAL_MESSAGE],
        primary: true
      }
    ]}
  >
    <ListData
      name={`${selectors.STORE_NAME}.sentMessages`}
      fetch={{
        url: ['apiv2_message_sent'],
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
          name: 'to',
          type: 'string',
          label: trans('message_form_to', {}, 'message'),
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
          icon: 'fa fa-fw fa-trash',
          label: trans('delete', {}, 'actions'),
          dangerous: true,
          callback: () => props.removeMessages(rows),
          confirm: {
            title: trans('messages_delete_title', {}, 'message'),
            message: trans('messages_delete_confirm', {}, 'message')
          }
        }
      ]}
      card={MessageCard}
    />
  </ToolPage>

MessageSentComponent.propTypes = {
  path: T.string.isRequired,
  removeMessages: T.func.isRequired
}

const MessageSent = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  dispatch => ({
    removeMessages(message) {
      dispatch(actions.removeMessages(message, `${selectors.STORE_NAME}.sentMessages`))
    }
  })
)(MessageSentComponent)


export {
  MessageSent
}
