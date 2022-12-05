import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {MODAL_ALERT} from '#/main/app/modals/alert'
import {actions as listActions} from '#/main/app/content/list/store'
import {withModal} from '#/main/app/overlays/modal/withModal'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {Subject as SubjectType} from '#/plugin/forum/resources/forum/player/prop-types'
import {selectors} from '#/plugin/forum/resources/forum/store'
import {actions} from '#/plugin/forum/resources/forum/player/store'
import {CommentForm, Comment} from '#/plugin/forum/resources/forum/player/components/comments'

class MessageCommentsComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      showCommentForm: null,
      showNewCommentForm: null,
      opened: props.opened
    }
  }

  toggleComments() {
    this.setState({opened: !this.state.opened})
    if (this.state.showNewCommentForm && this.state.opened === true) {
      this.setState({showNewCommentForm: null})
    }
  }

  showCommentForm(messageId) {
    this.setState({opened: true})
    this.setState({showNewCommentForm: messageId})
  }

  createNewComment(messageId, comment) {
    this.props.createComment(messageId, comment, this.props.forum.moderation)
    this.setState({showNewCommentForm: null})

    if (this.props.forum.moderation === 'PRIOR_ALL' || (this.props.forum.moderation === 'PRIOR_ONCE' && !this.props.isValidatedUser)) {
      this.props.showModal(MODAL_ALERT, {
        title: trans('moderated_posts', {}, 'forum'),
        message: trans('moderated_posts_explanation', {}, 'forum'),
        type: 'info'
      })
    }
  }

  updateComment(comment, content) {
    this.props.editContent(comment, this.props.subject.id, content)
    this.setState({showCommentForm: null})
  }

  render() {
    const visibleComments = this.props.message.children.filter(comment => 'NONE' === comment.meta.moderation)

    return (
      <div className="answer-comment-container">
        {(this.state.opened &&
          <div>
            {visibleComments.map(comment =>
              <div key={comment.id}>
                {this.state.showCommentForm !== comment.id &&
                  <Comment
                    user={comment.meta.creator}
                    date={comment.meta.created}
                    content={comment.content}
                    allowHtml={true}
                    actions={[
                      {
                        type: CALLBACK_BUTTON,
                        icon: 'fa fa-fw fa-pencil',
                        label: trans('edit', {}, 'actions'),
                        displayed: this.props.currentUser && (comment.meta.creator.id === this.props.currentUser.id) && !get(this.props.subject, 'meta.closed'),
                        callback: () => this.setState({showCommentForm: comment.id})
                      }, {
                        type: CALLBACK_BUTTON,
                        icon: 'fa fa-fw fa-flag',
                        label: trans('flag', {}, 'forum'),
                        displayed: this.props.currentUser && (comment.meta.creator.id !== this.props.currentUser.id) && !comment.meta.flagged,
                        callback: () => this.props.flag(comment, this.props.subject.id)
                      }, {
                        type: CALLBACK_BUTTON,
                        icon: 'fa fa-fw fa-flag',
                        label: trans('unflag', {}, 'forum'),
                        displayed: this.props.currentUser && (comment.meta.creator.id !== this.props.currentUser.id) && comment.meta.flagged,
                        callback: () => this.props.unFlag(comment, this.props.subject.id)
                      }, {
                        type: CALLBACK_BUTTON,
                        icon: 'fa fa-fw fa-trash',
                        label: trans('delete', {}, 'actions'),
                        displayed: this.props.currentUser && (comment.meta.creator.id === this.props.currentUser.id || this.props.moderator),
                        callback: () => this.props.deleteComment(comment.id),
                        dangerous: true,
                        confirm: {
                          title: trans('delete_comment', {}, 'forum'),
                          message: trans('remove_comment_confirm_message', {}, 'forum')
                        }
                      }
                    ]}
                  />
                }

                {this.state.showCommentForm === comment.id &&
                  <CommentForm
                    user={this.props.currentUser}
                    allowHtml={true}
                    submitLabel={trans('add_comment')}
                    content={comment.content}
                    submit={(content) => this.updateComment(comment, content)}
                    cancel={() => this.setState({showCommentForm: null})}
                  />
                }
              </div>
            )}
          </div>
        )}
        {this.state.showNewCommentForm === this.props.message.id &&
          <CommentForm
            user={this.props.currentUser}
            allowHtml={true}
            submitLabel={trans('add_comment')}
            // content={comment.content}
            submit={(comment) => this.createNewComment(this.props.message.id, comment)}
            cancel={() => this.setState({showNewCommentForm: null})}
          />
        }
        <div className="comment-link-container">
          {this.props.message.children.length !== 0 &&
            <button
              type="button"
              className="btn btn-link btn-sm comment-link"
              onClick={() => this.toggleComments()}
            >
              {this.state.opened ? transChoice('hide_comments',visibleComments.length, {count: visibleComments.length}, 'forum'): transChoice('show_comments', visibleComments.length, {count: visibleComments.length}, 'forum')}
            </button>
          }
          {(!this.props.bannedUser && !get(this.props.subject, 'meta.closed') && !this.state.showNewCommentForm) &&
            <button
              type="button"
              onClick={() => this.showCommentForm(this.props.message.id)}
              className='btn btn-link btn-sm comment-link'
            >
              {trans('comment', {}, 'actions')}
            </button>
          }
        </div>
      </div>
    )
  }
}

MessageCommentsComponent.propTypes = {
  currentUser: T.object,
  subject: T.shape(SubjectType.propTypes).isRequired,
  message: T.shape({
    id: T.string.Required,
    children: T.array.isRequired
  }).isRequired,
  forum: T.shape({
    moderation: T.string.isRequired
  }).isRequired,
  editContent: T.func.isRequired,
  opened: T.bool,
  flag: T.func.isRequired,
  unFlag: T.func.isRequired,
  deleteComment: T.func.isRequired,
  createComment: T.func.isRequired,
  showModal: T.func,
  bannedUser: T.bool.isRequired,
  moderator: T.bool.isRequired,
  isValidatedUser: T.bool.isRequired
}

MessageCommentsComponent.defaultProps = {
  bannedUser: false,
  isValidatedUser: false
}

const MessageComments =  withModal(connect(
  state => ({
    currentUser: securitySelectors.currentUser(state),
    forum: selectors.forum(state),
    isValidatedUser: selectors.isValidatedUser(state),
    subject: selectors.subject(state),
    bannedUser: selectors.bannedUser(state),
    moderator: selectors.moderator(state)
  }),
  dispatch => ({
    createComment(messageId, comment, moderation) {
      dispatch(actions.createComment(messageId, comment, moderation))
    },
    deleteComment(id) {
      dispatch(listActions.deleteData('subjects.messages', ['apiv2_forum_message_delete_bulk'], [{id: id}]))
    },
    reload(id, forumId) {
      dispatch(listActions.fetchData('subjects.messages', ['apiv2_forum_subject_get_message', {id, forumId}]))
    },
    editContent(message, subjectId, content) {
      dispatch(actions.editContent(message, subjectId, content))
    },
    flag(message, subjectId) {
      dispatch(actions.flag(message, subjectId))
    },
    unFlag(message, subjectId) {
      dispatch(actions.flag(message, subjectId))
    }
  })
)(MessageCommentsComponent))

export {
  MessageComments
}
