import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import merge from 'lodash/merge'

import {t} from '#/main/core/translation'
import {localeDate} from '#/main/core/layout/data/types/date/utils'

import {Action as ActionTypes} from '#/main/core/layout/button/prop-types'
import {User as UserTypes} from '#/main/core/layout/user/prop-types'

import {HtmlText} from '#/main/core/layout/components/html-text.jsx'
import {TooltipAction} from '#/main/core/layout/button/components/tooltip-action.jsx'

import {UserAvatar} from './user-avatar.jsx'

/**
 * Representation of a User message.
 * Can be used in comments, messages, etc.
 *
 * @todo maybe allow a title for forums
 *
 * @param props
 * @constructor
 */
const UserMessage = props => {
  const actions = props.actions.filter(action => action.displayed)

  return (
    <div className={classes('user-message-container', {
      'user-message-left': 'left' === props.position,
      'user-message-right': 'right' === props.position
    })}>
      {'left' === props.position &&
      <UserAvatar picture={props.user.picture} />
      }

      <div className="user-message">
        <div className="user-message-meta">
          <div className="user-message-info">
            {props.user && props.user.name ?
              props.user.name : t('unknown')
            }

            {props.date &&
            <div className="date">{t('published_at', {date: localeDate(props.date)})}</div>
            }
          </div>

          {0 !== actions.length &&
          <div className="user-message-actions">
            {actions.map((action, actionIndex) =>
              <TooltipAction
                key={`action-${actionIndex}`}
                id={`action-${actionIndex}`}
                className={action.dangerous ? 'btn-link-danger' : 'btn-link-default'}
                position="bottom"
                {...action}
              />
            )}
          </div>
          }
        </div>

        {React.createElement(
          props.allowHtml ? HtmlText : 'div',
          {className: 'user-message-content'},
          props.content
        )}
      </div>

      {'right' === props.position &&
        <UserAvatar picture={props.user.picture} />
      }
    </div>
  )
}

UserMessage.propTypes = {
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
  user: {},
  allowHtml: false,
  position: 'left',
  actions: []
}

export {
  UserMessage
}
