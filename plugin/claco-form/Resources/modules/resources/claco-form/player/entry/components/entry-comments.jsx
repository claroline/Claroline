import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {currentUser} from '#/main/core/user/current'
import {trans} from '#/main/core/translation'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {UserMessage} from '#/main/core/user/message/components/user-message.jsx'
import {UserMessageForm} from '#/main/core/user/message/components/user-message-form.jsx'

import {select} from '#/plugin/claco-form/resources/claco-form/selectors'
import {actions} from '#/plugin/claco-form/resources/claco-form/player/entry/actions'

const authenticatedUser = currentUser()

class EntryCommentsComponent extends Component {
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

  filterComment(comment) {
    return this.props.canManage || comment.status === 1 || (authenticatedUser && comment.user && authenticatedUser.id === comment.user.id)
  }

  canEditComment(comment) {
    return this.props.canManage || (authenticatedUser && comment.user && authenticatedUser.id === comment.user.id)
  }

  deleteComment(commentId) {
    this.props.showModal(MODAL_CONFIRM, {
      icon: 'fa fa-fw fa-trash-o',
      title: trans('delete_comment', {}, 'clacoform'),
      question: trans('delete_comment_confirm_message', {}, 'clacoform'),
      handleConfirm: () => this.props.deleteComment(commentId)
    })
  }

  createNewComment(comment) {
    this.props.createComment(this.props.entry.id, comment)
    this.setState({showNewCommentForm: false})
  }

  editComment(commentId, commentContent) {
    this.props.editComment(commentId, commentContent)

    this.setState({[commentId]: {showCommentForm: false}})
  }

  showCommentForm(comment) {
    this.setState({[comment.id]: {showCommentForm: true}})
  }

  cancelCommentEdition(commentId) {
    this.setState({[commentId]: {showCommentForm: false}})
  }

  render() {
    const comments = this.props.entry.comments ? this.props.entry.comments.filter(comment => this.filterComment(comment)) : []

    return (
      <section className={classes('comments-container', {
        opened: this.state.opened
      })}>
        <h3 className="comments-title">
          <span className="comments-icon">
            <span className="fa fa-fw fa-comments" />
            <span className="comments-count">{comments.length || '0'}</span>
          </span>

          {trans('comments', {}, 'clacoform')}

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
                {trans('add_comment', {}, 'clacoform')}
              </button>
            }

            {this.state.showNewCommentForm &&
              <h4>{trans('add_comment', {}, 'clacoform')}</h4>
            }

            {this.state.showNewCommentForm &&
              <UserMessageForm
                user={authenticatedUser}
                allowHtml={true}
                submitLabel={trans('add_comment')}
                submit={(comment) => this.createNewComment(comment)}
                cancel={() => this.setState({showNewCommentForm: false})}
              />
            }

            <hr/>
          </section>
        }

        {this.state.opened && this.props.canViewComments &&
          <section className="comments-section">
            <h4>{trans('all_comments', {}, 'clacoform')}</h4>

            {0 === comments.length &&
            <div className="list-empty">
              {trans('no_comment', {}, 'clacoform')}
            </div>
            }

            {comments.map((comment, commentIndex) =>
              this.state[comment.id] && this.state[comment.id].showCommentForm ?
                <UserMessageForm
                  key={`comment-${commentIndex}`}
                  user={authenticatedUser}
                  content={comment.content}
                  allowHtml={true}
                  submitLabel={trans('add_comment')}
                  submit={(commentContent) => this.editComment(comment.id, commentContent)}
                  cancel={() => this.cancelCommentEdition(comment.id)}
                /> :
                <UserMessage
                  key={`comment-${commentIndex}`}
                  className={classes({
                    'user-message-inactive': 0 === comment.status,
                    'user-message-blocked': 2 === comment.status
                  })}
                  user={this.props.displayCommentAuthor && comment.user ? comment.user : undefined}
                  date={this.props.displayCommentDate ? comment.creationDate : ''}
                  content={comment.content}
                  allowHtml={true}
                  actions={[
                    {
                      icon: 'fa fa-fw fa-pencil',
                      label: trans('edit'),
                      displayed: this.canEditComment(comment),
                      action: () => this.showCommentForm(comment)
                    }, {
                      icon: 'fa fa-fw fa-check',
                      label: trans('activate'),
                      displayed: this.props.canManage && (0 === comment.status || 2 === comment.status),
                      action: () => this.props.activateComment(comment.id)
                    }, {
                      icon: 'fa fa-fw fa-ban',
                      label: trans('block', {}, 'clacoform'),
                      displayed: this.props.canManage && 1 === comment.status,
                      action: () => this.props.blockComment(comment.id)
                    }, {
                      icon: 'fa fa-fw fa-trash-o',
                      label: trans('delete'),
                      displayed: this.props.canManage,
                      action: () => this.deleteComment(comment.id),
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

EntryCommentsComponent.propTypes = {
  opened: T.bool.isRequired,
  entry: T.object.isRequired,
  canComment: T.bool.isRequired,
  canViewComments: T.bool.isRequired,
  canManage: T.bool.isRequired,
  displayCommentAuthor: T.bool.isRequired,
  displayCommentDate: T.bool.isRequired,
  createComment: T.func.isRequired,
  editComment: T.func.isRequired,
  deleteComment: T.func.isRequired,
  activateComment: T.func.isRequired,
  blockComment: T.func.isRequired,
  showModal: T.func.isRequired
}

const EntryComments = connect(
  (state) => ({
    entry: formSelect.data(formSelect.form(state, 'entries.current')),
    displayCommentAuthor: select.getParam(state, 'display_comment_author'),
    displayCommentDate: select.getParam(state, 'display_comment_date')
  }),
  (dispatch) => ({
    createComment(entryId, content) {
      dispatch(actions.createComment(entryId, content))
    },
    editComment(commentId, content) {
      dispatch(actions.editComment(commentId, content))
    },
    deleteComment(commentId) {
      dispatch(actions.deleteComment(commentId))
    },
    activateComment(commentId) {
      dispatch(actions.activateComment(commentId))
    },
    blockComment(commentId) {
      dispatch(actions.blockComment(commentId))
    },
    showModal(type, props) {
      dispatch(modalActions.showModal(type, props))
    }
  })
)(EntryCommentsComponent)

export {
  EntryComments
}