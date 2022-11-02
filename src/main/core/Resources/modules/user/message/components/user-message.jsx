import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {User as UserTypes} from '#/main/community/prop-types'
import {ContentHtml} from '#/main/app/content/components/html'
import {ContentMessage} from '#/main/app/content/components/message'

const UserMessage = props =>
  <ContentMessage
    className={props.className}
    user={props.user}
    date={props.date}
    position={props.position}
    actions={props.actions}
  >
    {createElement(
      props.allowHtml ? ContentHtml : 'div',
      {className: 'user-message-content'},
      props.content
    )}
  </ContentMessage>

UserMessage.propTypes = {
  className: T.string,

  /**
   * The date of the message.
   *
   * @type {string}
   */
  date: T.string,

  /**
   * The user who have sent the message.
   *
   * @type {object}
   */
  user: T.shape(UserTypes.propTypes),

  /**
   * The object of the message.
   *
   * @type {string}
   */
  object: T.string,

  /**
   * The content of the message.
   *
   * @type {string}
   */
  content: T.string.isRequired,

  /**
   * Allow (or not) HTML in message content.
   *
   * @type {bool}
   */
  allowHtml: T.bool,

  /**
   * The position of the User avatar.
   *
   * @type {string}
   */
  position: T.oneOf(['left', 'right']),

  /**
   * The available actions for the message.
   *
   * @type {array}
   */
  actions: T.arrayOf(
    T.shape(merge({}, ActionTypes.propTypes, {
      displayed: T.bool.isRequired
    }))
  )
}

UserMessage.defaultProps = {
  allowHtml: false,
  position: 'left',
  actions: []
}

export {
  UserMessage
}
