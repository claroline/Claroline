import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {User as UserTypes} from '#/main/community/prop-types'

import {LinkButton} from '#/main/app/buttons/link/components/button'

import {route} from '#/main/community/user/routing'
import {UserAvatar} from '#/main/app/user/components/avatar'
import {displayUsername} from '#/main/community/utils'

/**
 * Representation of a User message.
 * Can be used in comments, messages, etc.
 */
const ContentMessage = props => {
  let SenderComponent
  if (props.user) {
    SenderComponent = (
      <LinkButton target={route(props.user)} className="user-message-sender">
        <UserAvatar user={props.user} size="md" />
      </LinkButton>
    )
  } else {
    SenderComponent = (
      <span className="user-message-sender">
        <UserAvatar size="md" />
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
            {displayUsername(props.user)}

            {props.date &&
              <div className="date">{trans('published_at', {date: displayDate(props.date, true, true)})}</div>
            }
          </div>

          {0 !== props.actions.length &&
            <Toolbar
              className="user-message-actions btn-toolbar"
              buttonName="btn btn-text-secondary"
              tooltip="bottom"
              toolbar="more"
              actions={props.actions}
            />
          }
        </div>

        {props.children}
      </div>
    </div>
  )
}

ContentMessage.propTypes = {
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
   * The content to display.
   *
   * @type {string}
   */
  children: T.node.isRequired,

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
      displayed: T.bool
    }))
  )
}

ContentMessage.defaultProps = {
  position: 'left',
  actions: []
}

export {
  ContentMessage
}
