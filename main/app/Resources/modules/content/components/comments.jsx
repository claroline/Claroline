import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {User as UserType} from '#/main/core/user/prop-types'
import {UserMessage} from '#/main/core/user/message/components/user-message'
import {UserMessageForm} from '#/main/core/user/message/components/user-message-form'

class Comments extends Component {
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
    this.props.createComment(content)
    this.setState({showNewCommentForm: false})
  }

  editComment(commentId, content) {
    this.props.editComment(commentId, content)

    this.setState({[commentId]: {showCommentForm: false}})
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

        {this.state.opened && this.props.canComment &&
          <section className="comments-section">
            {!this.state.showNewCommentForm &&
              <button
                className="btn btn-add-comment"
                onClick={() => this.setState({showNewCommentForm: true})}
              >
                <span className="fa fa-fw fa-edit" style={{marginRight: '7px'}} />
                {this.props.addCommentLabel}
              </button>
            }

            {this.state.showNewCommentForm &&
              <UserMessageForm
                user={this.props.currentUser}
                allowHtml={true}
                submitLabel={trans('add_comment')}
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
                  submitLabel={trans('add_comment')}
                  submit={(content) => this.editComment(comment.id, content)}
                  cancel={() => this.cancelCommentEdition(comment.id)}
                /> :
                <UserMessage
                  key={`comment-${commentIndex}`}
                  user={this.props.displayCommentAuthor && comment.user ? comment.user : undefined}
                  date={this.props.displayCommentDate ? comment.creationDate : ''}
                  content={comment.content}
                  allowHtml={true}
                  actions={[
                    {
                      icon: 'fa fa-fw fa-pencil',
                      type: CALLBACK_BUTTON,
                      label: trans('edit'),
                      displayed: this.props.canEditComment(comment),
                      callback: () => this.showCommentForm(comment)
                    }, {
                      icon: 'fa fa-fw fa-trash-o',
                      type: CALLBACK_BUTTON,
                      label: trans('delete'),
                      displayed: this.props.canManage,
                      callback: () => this.props.deleteComment(comment.id),
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

Comments.propTypes = {
  currentUser: T.object,
  title: T.string.isRequired,
  noCommentLabel: T.string.isRequired,
  addCommentLabel: T.string.isRequired,
  comments: T.arrayOf(T.shape({
    id: T.string.isRequired,
    content: T.string.isRequired,
    user: T.shape(UserType.propTypes)
  })).isRequired,
  opened: T.bool.isRequired,
  canComment: T.bool.isRequired,
  canViewComments: T.bool.isRequired,
  canManage: T.bool.isRequired,
  displayCommentAuthor: T.bool.isRequired,
  displayCommentDate: T.bool.isRequired,
  createComment: T.func.isRequired,
  editComment: T.func.isRequired,
  canEditComment: T.func.isRequired,
  deleteComment: T.func.isRequired
}

Comments.defaultProps = {
  title: trans('comments'),
  noCommentLabel: trans('no_comment'),
  addCommentLabel: trans('add_comment'),
  comments: [],
  opened: true,
  canComment: true,
  canViewComments: true,
  canManage: false,
  displayCommentAuthor: true,
  displayCommentDate: true,
  createComment: () => {},
  editComment: () => {},
  canEditComment: () => {},
  deleteComment: () => {}
}

export {
  Comments
}