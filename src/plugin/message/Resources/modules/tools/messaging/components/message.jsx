import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'

import {withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {now} from '#/main/app/intl/date'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentTitle} from '#/main/app/content/components/title'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {UserMessage} from '#/main/core/user/message/components/user-message'
import {UserMessageForm} from '#/main/core/user/message/components/user-message-form'

import {Message as MessageTypes} from '#/plugin/message/prop-types'
import {actions, selectors} from '#/plugin/message/tools/messaging/store'

function flattenMessages(root) {
  let messages = [root]

  if (root.children) {
    root.children.map(child => {
      messages = messages.concat(flattenMessages(child))
    })
  }

  return messages
}

class MessageComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      reply: false,
      all: false
    }

    this.reply = this.reply.bind(this)
  }

  reply(content) {
    this.props.reply(merge({}, MessageTypes.defaultProps, {
      parent: this.props.message,
      from: this.props.currentUser,
      object: `Re: ${this.props.message.object}`,
      content: content,
      receivers: this.state.all ? this.props.message.receivers : {
        users: [this.props.message.from]
      },
      meta: {date : now(false)}
    })).then(() => this.setState({reply: false, all: false}))
  }

  render() {
    if (isEmpty(this.props.message)) {
      return (
        <ContentLoader
          size="lg"
          description="Nous chargeons la conversation..."
        />
      )
    }

    const messages = flattenMessages(this.props.message)

    return (
      <Fragment>
        <ContentTitle
          level={2}
          title={this.props.message.object}
        />

        {messages
          .filter(message => !get(message, 'meta.removed') || message.id === this.props.currentId)
          .sort((a, b) => {
            if (get(a, 'meta.date') > get(b, 'meta.date')) {
              return 1
            }

            return - 1
          })
          .map(message =>
            <UserMessage
              className={classes({
                'user-message-highlight': 1 < messages.length && message.id === this.props.currentId
              })}
              key={`message-${message.id}`}
              user={get(message, 'from')}
              date={get(message, 'meta.date')}
              content={message.content}
              allowHtml={true}
              actions={[
                {
                  name: 'restore',
                  type: CALLBACK_BUTTON,
                  icon: 'fa fa-fw fa-sync-alt',
                  label: trans('restore', {}, 'actions'),
                  displayed: get(message, 'meta.removed'),
                  callback: () => this.props.restore(message),
                  confirm: {
                    title: trans('messages_restore_title', {}, 'message'),
                    message: trans('messages_confirm_restore', {}, 'message')
                  }
                }, {
                  name: 'hard-delete',
                  type: CALLBACK_BUTTON,
                  icon: 'fa fa-fw fa-trash',
                  label: trans('delete', {}, 'actions'),
                  callback: () => this.props.delete(message, this.props.history.push, this.props.path),
                  dangerous: true,
                  displayed: get(message, 'meta.removed'),
                  confirm: {
                    title: trans('messages_delete_title', {}, 'message'),
                    message: trans('messages_delete_confirm_permanent', {}, 'message')
                  }
                }, {
                  name: 'soft-delete',
                  type: CALLBACK_BUTTON,
                  icon: 'fa fa-fw fa-trash',
                  label: trans('delete', {}, 'actions'),
                  callback: () => this.props.remove(message, this.props.history.push, this.props.path),
                  dangerous: true,
                  displayed: !get(message, 'meta.removed'),
                  confirm: {
                    title: trans('messages_delete_title', {}, 'message'),
                    message: trans('remove_message_confirm_message', {}, 'message')
                  }
                }
              ]}
            />
          )
        }

        {(!this.state.reply && !get(this.props.message, 'meta.sent') && !get(this.props.message, 'meta.removed')) &&
          <Button
            className="btn btn-block btn-emphasis"
            type={CALLBACK_BUTTON}
            label={trans('reply', {}, 'actions')}
            callback={() => this.setState({reply: true, all: false})}
            primary={true}
          />
        }

        {(!this.state.reply && !get(this.props.message, 'meta.sent') && !get(this.props.message, 'meta.removed')) &&
          <Button
            className="btn btn-block"
            type={CALLBACK_BUTTON}
            label={trans('reply-all', {}, 'actions')}
            callback={() => this.setState({reply: true, all: true})}
          />
        }

        {this.state.reply &&
          <UserMessageForm
            user={this.props.currentUser}
            allowHtml={true}
            submitLabel={trans(this.state.all ? 'reply-all' : 'reply', {}, 'actions')}
            submit={this.reply}
            cancel={() => this.setState({reply: false, all: false})}
          />
        }
      </Fragment>
    )
  }
}

MessageComponent.propTypes = {
  path: T.string.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  currentUser: T.shape({
    // TODO : user types
  }),
  message: T.shape(
    MessageTypes.propTypes
  ),
  currentId: T.string.isRequired,
  restore: T.func.isRequired,
  remove: T.func.isRequired,
  delete: T.func.isRequired,
  reply: T.func.isRequired
}

const Message = withRouter(
  connect(
    state => ({
      currentUser: securitySelectors.currentUser(state),
      path: toolSelectors.path(state),
      message: selectors.message(state)
    }),
    dispatch => ({
      reply(message) {
        return dispatch(actions.sendMessage(message))
      },
      delete(message, push, path) {
        dispatch(actions.deleteMessages([message])).then(() => push(`${path}/received`))
      },
      remove(message, push, path) {
        dispatch(actions.removeMessages([message])).then(() => push(`${path}/received`))
      },
      restore(message) {
        dispatch(actions.restoreMessages([message]))
      }
    })
  )(MessageComponent)
)

export {
  Message
}
