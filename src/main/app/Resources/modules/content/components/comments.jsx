import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {User as UserType} from '#/main/core/user/prop-types'
import {UserMessage} from '#/main/core/user/message/components/user-message'
import {UserMessageForm} from '#/main/core/user/message/components/user-message-form'

class ContentComments extends Component {
  constructor(props) {
    super(props)

    this.state = {
      opened: props.opened,
      showNewCommentForm: false
    }
  }

  toggleComments() {
    this.setState({opened: !this.state.opened})
  }

  createNewComment(content) {
    this.props.createComment({
      user: this.props.currentUser,
      content: content
    })
    this.setState({showNewCommentForm: false})
  }

  editComment(comment, content) {
    this.props.editComment(merge({}, comment, {
      content: content
    }))

    this.setState({[comment.id]: {showCommentForm: false}})
  }

  showCommentForm(comment) {
    this.setState({[comment.id]: {showCommentForm: true}})
  }

  cancelCommentEdition(commentId) {
    this.setState({[commentId]: {showCommentForm: false}})
  }

  render() {
    return (
      <section className={classes('comments-container', {
        opened: this.state.opened
      })}>
        <h3 className="comments-title">
          <span className="comments-icon">
            <span className="fa fa-fw fa-comments" />
            <span className="comments-count">{this.props.comments.length || '0'}</span>
          </span>

          {this.props.title}

          <button
            type="button"
            className="btn btn-link btn-sm btn-toggle-comments"
            onClick={() => this.toggleComments()}
          >
            {trans(this.state.opened ? 'hide':'show')}
          </button>
        </h3>

        {this.state.opened && this.props.canComment && !!this.props.createComment &&
          <section className="comments-section">
            {!this.state.showNewCommentForm &&
              <button
                className="btn btn-add-comment"
                onClick={() => this.setState({showNewCommentForm: true})}
              >
                <span className="fa fa-fw fa-edit icon-with-text-right" />
                {this.props.addCommentLabel}
              </button>
            }

            {this.state.showNewCommentForm &&
              <UserMessageForm
                user={this.props.currentUser}
                allowHtml={true}
                submitLabel={trans('comment', {}, 'actions')}
                submit={(content) => this.createNewComment(content)}
                cancel={() => this.setState({showNewCommentForm: false})}
              />
            }

            <hr/>
          </section>
        }

        {this.state.opened && this.props.canViewComments &&
          <section className="comments-section">
            {0 === this.props.comments.length &&
              <div className="list-empty">
                {this.props.noCommentLabel}
              </div>
            }

            {this.props.comments.map((comment, commentIndex) =>
              this.state[comment.id] && this.state[comment.id].showCommentForm ?
                <UserMessageForm
                  key={`comment-${commentIndex}`}
                  user={this.props.currentUser}
                  content={comment.content}
                  allowHtml={true}
                  submitLabel={trans('edit', {}, 'actions')}
                  submit={(content) => this.editComment(comment, content)}
                  cancel={() => this.cancelCommentEdition(comment.id)}
                /> :
                <UserMessage
                  key={`comment-${commentIndex}`}
                  user={comment.user}
                  date={comment.creationDate}
                  content={comment.content}
                  allowHtml={true}
                  actions={[
                    {
                      name: 'edit',
                      icon: 'fa fa-fw fa-pencil',
                      type: CALLBACK_BUTTON,
                      label: trans('edit'),
                      displayed: !!this.props.editComment && this.props.currentUser && comment.user.id === this.props.currentUser.id,
                      callback: () => this.showCommentForm(comment)
                    }, {
                      name: 'delete',
                      icon: 'fa fa-fw fa-trash-o',
                      type: CALLBACK_BUTTON,
                      label: trans('delete'),
                      displayed: !!this.props.deleteComment && (this.props.canManage || this.props.currentUser && comment.user.id === this.props.currentUser.id),
                      callback: () => this.props.deleteComment(comment),
                      dangerous: true
                    }
                  ]}
                />
            )}
          </section>
        }
      </section>
    )
  }
}

ContentComments.propTypes = {
  currentUser: T.object,
  title: T.string,
  noCommentLabel: T.string.isRequired,
  addCommentLabel: T.string.isRequired,
  comments: T.arrayOf(T.shape({
    id: T.string.isRequired,
    content: T.string.isRequired,
    user: T.shape(UserType.propTypes),
    creationDate: T.string
  })),
  opened: T.bool,
  canComment: T.bool,
  canViewComments: T.bool,
  canManage: T.bool,

  createComment: T.func,
  editComment: T.func,
  deleteComment: T.func
}

ContentComments.defaultProps = {
  title: trans('comments'),
  noCommentLabel: trans('no_comment'),
  addCommentLabel: trans('add_comment'),
  comments: [],
  opened: true,
  canComment: true,
  canViewComments: true,
  canManage: false
}

export {
  ContentComments
}
