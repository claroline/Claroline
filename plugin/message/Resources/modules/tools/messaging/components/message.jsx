import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {UserMessage} from '#/main/core/user/message/components/user-message'

import {actions, selectors} from '#/plugin/message/tools/messaging/store'

const MessageComponent = (props) => {
  if (isEmpty(props.message)) {
    return (
      <ContentLoader
        size="lg"
        description="Nous chargeons la conversation"
      />
    )
  }

  return (
    <Fragment>
      <h2 className="h-title">
        <Button
          className="btn h-back"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-arrow-left"
          label={trans('back')}
          tooltip="bottom"
          callback={() => true}
        />

        {props.message.object}
      </h2>

      <UserMessage
        user={get(props.message, 'from')}
        date={get(props.message, 'meta.date')}
        content={props.message.content}
        allowHtml={true}
        actions={[
          {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-sync-alt',
            label: trans('restore', {}, 'actions'),
            displayed: get(props.message, 'meta.removed'),
            callback: () => props.restore(props.message),
            confirm: {
              title: trans('messages_restore_title', {}, 'message'),
              message: trans('messages_confirm_restore', {}, 'message')
            }
          }, {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-trash-o',
            label: trans('delete', {}, 'actions'),
            callback: () => props.remove(props.message),
            dangerous: true,
            displayed: get(props.message, 'meta.removed'),
            confirm: {
              title: trans('messages_delete_title', {}, 'message'),
              message: trans('remove_message_confirm_message', {}, 'message')
            }
          }, {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-trash-o',
            label: trans('delete', {}, 'actions'),
            callback: () => props.delete(props.message, props.history.push, props.path),
            dangerous: true,
            displayed: !get(props.message, 'meta.removed'),
            confirm: {
              title: trans('messages_delete_title', {}, 'message'),
              message: trans('messages_delete_confirm_permanent', {}, 'message')
            }
          }
        ]}
      />

      {(!get(props.message, 'meta.sent') && !get(props.message, 'meta.removed')) &&
        <Button
          className="btn btn-block btn-emphasis"
          type={CALLBACK_BUTTON}
          label={trans('reply', {}, 'actions')}
          callback={() => true}
          primary={true}
        />
      }

      {(!get(props.message, 'meta.sent') && !get(props.message, 'meta.removed')) &&
        <Button
          className="btn btn-block"
          type={CALLBACK_BUTTON}
          label={trans('reply-all', {}, 'actions')}
          callback={() => true}
        />
      }
    </Fragment>
  )
}

MessageComponent.propTypes = {
  path: T.string.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  message: T.shape({
    content: T.string,
    object: T.string.isRequired
  }),
  restore: T.func.isRequired,
  remove: T.func.isRequired,
  delete: T.func.isRequired
}

const Message = connect(
  state => ({
    path: toolSelectors.path(state),
    message: selectors.message(state)
  }),
  dispatch => ({
    delete(message, push, path) {
      dispatch(actions.deleteMessages([message])).then(() => push(`${path}/received`))
    },
    remove(message) {
      dispatch(actions.removeMessages([message]))
    },
    restore(message) {
      dispatch(actions.restoreMessages([message]))
    }
  })
)(MessageComponent)

export {
  Message
}
