import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'


import {trans, transChoice} from '#/main/core/translation'
import {currentUser} from '#/main/core/user/current'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {MODAL_ALERT} from '#/main/app/modals/alert'
import {actions as listActions} from '#/main/core/data/list/actions'
import {withModal} from '#/main/app/overlay/modal/withModal'

import {Subject as SubjectType} from '#/plugin/forum/resources/forum/player/prop-types'
import {select} from '#/plugin/forum/resources/forum/selectors'
import {actions} from '#/plugin/forum/resources/forum/player/actions'
import {CommentForm, Comment} from '#/plugin/forum/resources/forum/player/components/comments'

const authenticatedUser = currentUser()

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
    if(this.state.showNewCommentForm && this.state.opened === true) {
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
    if(this.props.forum.moderation === 'PRIOR_ALL' ||
    this.props.forum.moderation === 'PRIOR_ONCE' ) {
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

  deleteComment(commentId) {
    this.props.showModal(MODAL_CONFIRM, {
      dangerous: true,
      icon: 'fa fa-fw fa-trash-o',
      title: trans('delete_comment', {}, 'forum'),
      question: trans('remove_comment_confirm_message', {}, 'forum'),
      handleConfirm: () => this.props.deleteComment(commentId)
    })
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
                        icon: 'fa fa-fw fa-pencil',
                        label: trans('edit'),
                        displayed: (comment.meta.creator.id === authenticatedUser.id) && !get(this.props.subject, 'meta.closed'),
                        action: () => this.setState({showCommentForm: comment.id})
                      }, {
                        icon: 'fa fa-fw fa-flag-o',
                        label: trans('flag', {}, 'forum'),
                        displayed: (comment.meta.creator.id !== authenticatedUser.id) && !comment.meta.flagged,
                        action: () => this.props.flag(comment, this.props.subject.id)
                      }, {
                        icon: 'fa fa-fw fa-flag',
                        label: trans('unflag', {}, 'forum'),
                        displayed: (comment.meta.creator.id !== authenticatedUser.id) && comment.meta.flagged,
                        action: () => this.props.unFlag(comment, this.props.subject.id)
                      }, {
                        icon: 'fa fa-fw fa-trash-o',
                        label: trans('delete'),
                        displayed: comment.meta.creator.id === authenticatedUser.id || this.props.moderator,
                        action: () => this.deleteComment(comment.id),
                        dangerous: true
                      }
                    ]}
                  />
                }
                {this.state.showCommentForm === comment.id &&
                  <CommentForm
                    user={currentUser()}
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
            user={currentUser()}
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
  bannedUser: T.bool.isRrequired,
  moderator: T.bool.isRrequired
}

const MessageComments =  withModal(connect(
  state => ({
    forum: select.forum(state),
    subject: select.subject(state),
    bannedUser: select.bannedUser(state),
    moderator: select.moderator(state)
  }),
  dispatch => ({
    createComment(messageId, comment, moderation) {
      dispatch(actions.createComment(messageId, comment, moderation))
    },
    deleteComment(id) {
      dispatch(listActions.deleteData('subjects.messages', ['apiv2_forum_message_delete_bulk'], [{id: id}]))
    },
    reload(id) {
      dispatch(listActions.fetchData('subjects.messages', ['claroline_forum_api_subject_getmessages', {id}]))
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
