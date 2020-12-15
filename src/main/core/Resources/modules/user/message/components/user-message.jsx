import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {toKey} from '#/main/core/scaffolding/text'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Button} from '#/main/app/action/components/button'
import {User as UserTypes} from '#/main/core/user/prop-types'

import {LinkButton} from '#/main/app/buttons/link/components/button'
import {ContentHtml} from '#/main/app/content/components/html'

import {route} from '#/main/core/user/routing'
import {UserAvatar} from '#/main/core/user/components/avatar'

// TODO : buttons toolbar

/**
 * Representation of a User message.
 * Can be used in comments, messages, etc.
 *
 * @param props
 * @constructor
 */
const UserMessage = props => {
  const actions = props.actions.filter(action => action.displayed)

  let SenderComponent
  if (props.user) {
    SenderComponent = (
      <LinkButton target={route(props.user)} className="user-message-sender">
        <UserAvatar picture={props.user && props.user.picture} alt={false} />
      </LinkButton>
    )
  } else {
    SenderComponent = (
      <span className="user-message-sender">
        <UserAvatar alt={false} />
      </span>
    )
  }

  return (
    <div className={classes('user-message-container', props.className, {
      'user-message-left': 'left' === props.position,
      'user-message-right': 'right' === props.position
    })}>
      {SenderComponent}

      <div className="user-message">
        <div className="user-message-meta">
          <div className="user-message-info">
            {props.user && props.user.name ?

              props.user.name : trans('anonymous')
            }

            {props.date &&
              <div className="date">{trans('published_at', {date: displayDate(props.date, true, true)})}</div>
            }
          </div>

          {0 !== actions.length &&
            <div className="user-message-actions">
              {actions.map((action) =>
                <Button
                  key={action.id || toKey(action.label)}
                  className="btn btn-link"
                  tooltip="bottom"
                  {...action}
                />
              )}
            </div>
          }
        </div>

        {React.createElement(
          props.allowHtml ? ContentHtml : 'div',
          {className: 'user-message-content'},
          props.content
        )}
      </div>
    </div>
  )
}

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
