import React, {Fragment} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {UserMessage} from '#/main/core/user/message/components/user-message'

import {actions, selectors} from '#/plugin/message/tools/messaging/store'
import {NewMessage} from '#/plugin/message/tools/messaging/components/new-message'

const MessageComponent = (props) =>
  <Fragment>
    <h2>{props.message.object}</h2>
    <UserMessage
      user={get(props.message, 'from')}
      date={get(props.message, 'meta.date')}
      content={props.message.content}
      allowHtml={true}
      actions={[
        {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-sync-alt',
          label: trans('restore'),
          displayed: get(props.message, 'meta.removed'),
          callback: () => props.restoreMessage([props.message])
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash-o',
          label: trans('delete'),
          callback: () => props.removeMessage([props.message]),
          dangerous: true,
          displayed: get(props.message, 'meta.removed')
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash-o',
          label: trans('delete'),
          callback: () => props.deleteMessage([props.message], props.history.push, props.path),
          dangerous: true,
          displayed: !get(props.message, 'meta.removed')
        }
      ]}
    />

    {(!get(props.message, 'meta.sent') && !get(props.message, 'meta.removed')) &&
      <NewMessage/>
    }
  </Fragment>

MessageComponent.propTypes = {
  path: T.string.isRequired,
  message: T.shape({
    content: T.string,
    object: T.string.isRequired
  }),
  restoreMessage: T.func.isRequired,
  removeMessage: T.func.isRequired,
  deleteMessage: T.func.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired
}

MessageComponent.defaultProps = {
  message: {
    content: '',
    meta : {
      removed: true,
      sent: true
    }
  }
}
const Message = connect(
  state => ({
    path: toolSelectors.path(state),
    message: selectors.message(state)
  }),
  dispatch => ({
    deleteMessage(message, push, path) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          title: trans('messages_delete_title', {}, 'message'),
          question: trans('messages_delete_confirm_permanent', {}, 'message'),
          dangerous: true,
          handleConfirm: () => {
            dispatch(actions.deleteMessages(message))
              .then(() => push(`${path}/received`))
          }
        })
      )
    },
    removeMessage(message) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          title: trans('messages_delete_title', {}, 'message'),
          question: trans('remove_message_confirm_message', {}, 'message'),
          dangerous: true,
          handleConfirm: () => dispatch(actions.removeMessages(message))
        })
      )
    },
    restoreMessage(message) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          title: trans('messages_restore_title', {}, 'message'),
          question: trans('messages_confirm_restore', {}, 'message'),
          handleConfirm: () => dispatch(actions.restoreMessages(message))
        })
      )
    }
  })
)(MessageComponent)

export {
  Message
}
