import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import merge from 'lodash/merge'

import {trans} from '#/main/core/translation'
import {displayDate} from '#/main/core/scaffolding/date'
import {Action as ActionTypes} from '#/main/core/layout/action/prop-types'
import {User as UserTypes} from '#/main/core/user/prop-types'
import {TooltipAction} from '#/main/core/layout/button/components/tooltip-action.jsx'
import {HtmlGroup} from '#/main/core/layout/form/components/group/html-group.jsx'
import {TextGroup} from '#/main/core/layout/form/components/group/text-group.jsx'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'
import {UserAvatar} from '#/main/core/user/components/avatar.jsx'

class CommentForm extends Component {
  constructor(props) {
    super(props)

    this.state = {
      pendingChanges: false,
      content: props.content
    }
  }

  updateContent(content) {
    this.setState({
      pendingChanges: true,
      content: content
    })
  }

  render() {
    return (
      <div className={classes('user-comment-container user-comment-form-container', this.props.className, {
        'user-comment-left': 'left' === this.props.position,
        'user-comment-right': 'right' === this.props.position
      })}>
        {'left' === this.props.position &&
          <UserAvatar picture={this.props.user.picture} alt={false} />
        }
        <div className="user-comment">
          <div className="user-comment-meta">
            <div className="user-comment-info">
              {this.props.user && this.props.user.name ?
                this.props.user.name : trans('unknown')
              }
            </div>
            <div className="user-comment-actions">
              <TooltipAction
                id="close"
                className="btn-link-default"
                position="bottom"
                icon="fa fa-fw fa-times"
                label={trans('cancel')}
                action={this.props.cancel}
              />
            </div>
          </div>

          {React.createElement(
            this.props.allowHtml ? HtmlGroup : TextGroup,
            {
              id: 'user-comment-content',
              label: trans('comment'),
              hideLabel: true,
              value: this.state.content,
              long: true,
              onChange: value => this.updateContent(value)
            }
          )}
          <div className="btn-save-container">
            <button
              className="btn btn-block btn-primary btn-save"
              disabled={!this.state.pendingChanges || !this.state.content}
              onClick={() => this.props.submit(this.state.content)}
            >
              {trans('add_comment', {}, 'forum')}
            </button>
          </div>
        </div>

        {'right' === this.props.position &&
          <UserAvatar picture={this.props.user.picture} alt={false} />
        }
      </div>
    )
  }
}

CommentForm.propTypes = {
  className: T.string,

  /**
   * The user who have sent the comment.
   *
   * @type {object}
   */
  user: T.shape(UserTypes.propTypes),

  /**
   * The content of the comment.
   *
   * @type {string}
   */
  content: T.string,

  /**
   * Allow (or not) HTML in comment content.
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

  submitLabel: T.string,
  submit: T.func.isRequired,
  cancel: T.func
}

CommentForm.defaultProps = {
  className: '',
  user: {},
  content: '',
  allowHtml: false,
  position: 'left',
  submitLabel: trans('create')
}



const Comment = props => {
  const actions = props.actions.filter(action => action.displayed)

  return (
    <div className={classes('user-comment-container', {
      'user-comment-left': 'left' === props.position,
      'user-comment-right': 'right' === props.position
    })}>
      {'left' === props.position &&
        <UserAvatar picture={props.user.picture} alt={false} />
      }

      <div className="user-comment">
        <div className="user-comment-meta">
          <div className="user-comment-info">
            {props.user && props.user.name ?
              props.user.name : trans('unknown')
            }

            {props.date &&
              <div className="date">{trans('published_at', {date: displayDate(props.date, true, true)})}</div>
            }
          </div>

          {0 !== actions.length &&
            <div className="user-comment-actions">
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
          {className: 'user-comment-content'},
          props.content
        )}
      </div>

      {'right' === props.position &&
        <UserAvatar picture={props.user.picture} alt={false} />
      }
    </div>
  )
}

Comment.propTypes = {
  /**
   * The date of the comment.
   *
   * @type {string}
   */
  date: T.string,

  /**
   * The user who have sent the comment.
   *
   * @type {object}
   */
  user: T.shape(UserTypes.propTypes),

  /**
   * The object of the comment.
   *
   * @type {string}
   */
  object: T.string,

  /**
   * The content of the comment.
   *
   * @type {string}
   */
  content: T.string.isRequired,

  /**
   * Allow (or not) HTML in comment content.
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
   * The available actions for the comment.
   *
   * @type {array}
   */
  actions: T.arrayOf(
    T.shape(merge({}, ActionTypes.propTypes, {
      displayed: T.bool.isRequired
    }))
  )
}

Comment.defaultProps = {
  user: {},
  allowHtml: false,
  position: 'left',
  actions: []
}

export {
  CommentForm,
  Comment
}
