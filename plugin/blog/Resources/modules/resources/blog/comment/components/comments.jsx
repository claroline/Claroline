import React from 'react'
import {connect} from 'react-redux'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import {PropTypes as T} from 'prop-types'
import {UserMessage} from '#/main/core/user/message/components/user-message.jsx'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {currentUser} from '#/main/core/user/current'
import {hasPermission} from '#/main/core/resource/permissions'
import {UserMessageForm} from '#/main/core/user/message/components/user-message-form.jsx'
import {t, trans} from '#/main/core/translation'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {actions as commentActions} from '#/plugin/blog/resources/blog/comment/store'

const authenticatedUser = currentUser()

const CommentsComponent = props =>
  <div>
    <section>
      <h4 className="comments-title">
        <span className="comments-icon">
          <span className="fa fa-fw fa-comments" />
          <span className="comments-count">{props.comments.length || '0'}</span>
        </span>
        {trans('comments', {}, 'icap_blog')}
        <button
          type="button"
          className="btn btn-link btn-sm btn-toggle-comments"
          onClick={() => props.switchCommentsDisplay(!props.opened)}
        >
          {t(props.opened ? 'hide':'show')}
        </button>
      </h4>

      {props.opened && props.canComment &&
        <section className="comments-section">
          {!props.showComments  && !props.showForm &&
            <button
              className="btn btn-add-comment"
              onClick={() => props.switchCommentFormDisplay(!props.showForm)}
            >
              <span className="fa fa-fw fa-edit" style={{marginRight: '7px'}} />
              {trans('add_comment', {}, 'icap_blog')}
            </button>
          }
          {props.showForm &&
            <div>
              <h4>{trans('add_comment', {}, 'icap_blog')}</h4>
              <UserMessageForm
                user={authenticatedUser !== null ? authenticatedUser : {}}
                allowHtml={true}
                submitLabel={t('add_comment')}
                submit={(comment) => props.submitComment(props.blogId, props.postId, comment)}
                cancel={() => props.switchCommentFormDisplay(false)}
              />
            </div>
          }
          <hr/>
        </section>
      }
      {props.opened  &&
        <section className="comments-section">
          <h4>{trans('all_comments', {}, 'icap_blog')}</h4>

          {props.comments.length === 0 &&
          <div className="list-empty">
            {trans('no_comment', {}, 'icap_blog')}
          </div>
          }

          {props.comments.map((comment, commentIndex) =>
            !isEmpty(props.showEditCommentForm) && props.showEditCommentForm === comment.id ?
              <UserMessageForm
                key={`comment-${commentIndex}`}
                user={comment.author}
                content={comment.message}
                allowHtml={true}
                submitLabel={t('add_comment')}
                submit={(commentContent) => props.editComment(props.blogId,  props.postId, comment.id, commentContent)}
                cancel={() => props.switchEditCommentFormDisplay(false)}
              /> :
              <div key={`comment-container-${commentIndex}`} className={classes({'unpublished': !comment.isPublished}, 'comment')}>
                <UserMessage
                  key={`comment-${commentIndex}`}
                  user={comment.author ? comment.author : undefined}
                  date={comment.creationDate}
                  content={comment.message}
                  allowHtml={true}
                  actions={[
                    {
                      icon: 'fa fa-fw fa-pencil',
                      label: t('edit'),
                      displayed: props.canEdit || (comment.author !== null && authenticatedUser !== null && comment.author.id === authenticatedUser.id && !comment.isPublished),
                      action: () => props.switchEditCommentFormDisplay(comment.id)
                    },{
                      icon: 'fa fa-fw fa-check',
                      label: trans('icap_blog_post_publish', {}, 'icap_blog'),
                      displayed: (props.canEdit ||  props.canModerate) && !comment.isPublished,
                      action: () => props.publishComment(props.blogId, props.postId, comment.id)
                    },{
                      icon: 'fa fa-fw fa-ban',
                      label: trans('icap_blog_post_unpublish', {}, 'icap_blog'),
                      displayed: (props.canEdit ||  props.canModerate) && comment.isPublished,
                      action: () => props.unpublishComment(props.blogId, props.postId, comment.id)
                    },{
                      icon: 'fa fa-fw fa-flag',
                      label: trans('icap_blog_comment_report', {}, 'icap_blog'),
                      displayed: authenticatedUser !== null,
                      action: () => props.reportComment(props.blogId, props.postId, comment.id),
                      dangerous: true
                    },{
                      icon: 'fa fa-fw fa-trash',
                      label: t('delete'),
                      displayed: props.canEdit || (comment.author !== null && authenticatedUser !== null && comment.author.id === authenticatedUser.id && !comment.isPublished),
                      action: () => props.deleteComment(props.blogId, props.postId, comment.id),
                      dangerous: true
                    }
                  ]}
                />
              </div>
          )}
        </section>
      }
    </section>
  </div>
        
CommentsComponent.propTypes = {
  showEditCommentForm: T.string,
  blogId: T.string.isRequired,
  postId: T.string.isRequired,
  canEdit: T.bool,
  canModerate: T.bool,
  canComment: T.bool,
  showComments: T.bool,
  opened: T.bool,
  showForm: T.bool,
  comments: T.array,
  switchEditCommentFormDisplay: T.func.isRequired,
  switchCommentFormDisplay: T.func.isRequired,
  switchCommentsDisplay: T.func.isRequired,
  publishComment: T.func.isRequired,
  unpublishComment: T.func.isRequired,
  deleteComment: T.func.isRequired,
  reportComment: T.func.isRequired,
  submitComment: T.func.isRequired
}
        
const Comments = connect(
  state => ({
    user: state.user,
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
    canModerate: hasPermission('moderate', resourceSelect.resourceNode(state)),
    opened: state.showComments,
    showForm: state.showCommentForm,
    showEditCommentForm: state.showEditCommentForm
  }),
  dispatch => ({
    switchCommentsDisplay: (val) => {
      dispatch(commentActions.showComments(val))
    },
    switchCommentFormDisplay: (val) => {
      dispatch(commentActions.showCommentForm(val))
    },
    switchEditCommentFormDisplay: (val) => {
      dispatch(commentActions.showEditCommentForm(val))
    },
    submitComment: (blogId, postId, comment) => {
      dispatch(commentActions.submitComment(blogId, postId, comment))
    },
    editComment: (blogId, postId, commentId, comment) => {
      dispatch(commentActions.editComment(blogId, postId, commentId, comment))
    },
    publishComment: (blogId, postId, commentId) => {
      dispatch(commentActions.publishComment(blogId, postId, commentId))
    },
    unpublishComment: (blogId, postId, commentId) => {
      dispatch(commentActions.unpublishComment(blogId, postId, commentId))
    },
    reportComment: (blogId, postId, commentId) => {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        title: trans('comment_report_confirm_title', {}, 'icap_blog'),
        question: trans('comment_report_confirm_message', {}, 'icap_blog'),
        handleConfirm: () => dispatch(commentActions.reportComment(blogId, postId, commentId))
      }))
    },
    deleteComment: (blogId, postId, commentId) => {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        title: trans('comment_deletion_confirm_title', {}, 'icap_blog'),
        question: trans('comment_deletion_confirm_message', {}, 'icap_blog'),
        handleConfirm: () => dispatch(commentActions.deleteComment(blogId, postId, commentId))
      }))
    }
  })
)(CommentsComponent) 
    
export {Comments}