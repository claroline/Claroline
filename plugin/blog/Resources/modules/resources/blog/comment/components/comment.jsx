import React from 'react'
import {connect} from 'react-redux'
import classes from 'classnames'
import {PropTypes as T} from 'prop-types'
import {currentUser} from '#/main/core/user/current'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {actions as commentActions} from '#/plugin/blog/resources/blog/comment/store'
import {hasPermission} from '#/main/core/resource/permissions'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {UserMessage} from '#/main/core/user/message/components/user-message.jsx'
import {t, trans} from '#/main/core/translation'
import isEmpty from 'lodash/isEmpty'
import {UserMessageForm} from '#/main/core/user/message/components/user-message-form.jsx'
import {selectors} from '#/plugin/blog/resources/blog/store'

const authenticatedUser = currentUser()

const CommentComponent = (props) =>
  <div key={`comment-container-${props.comment.id}`} className={classes({'unpublished': !props.comment.isPublished}, 'comment')}>
    {!isEmpty(props.showEditCommentForm) && props.showEditCommentForm === props.comment.id ?
      <UserMessageForm
        key={`comment-${props.comment.id}`}
        user={props.comment.author ? props.comment.author : undefined}
        content={props.comment.message}
        allowHtml={true}
        submitLabel={t('add_comment')}
        submit={(commentContent) => props.editComment(props.blogId, props.comment.id, commentContent)}
        cancel={() => props.switchEditCommentFormDisplay('')}
      /> :
      <UserMessage
        key={`comment-${props.comment.id}`}
        user={props.comment.author ? props.comment.author : undefined}
        date={props.comment.creationDate}
        content={props.comment.message}
        allowHtml={true}
        actions={[
          {
            icon: 'fa fa-fw fa-pencil',
            label: t('edit'),
            displayed: props.showEdit && (props.canEdit || (props.comment.author !== null && authenticatedUser !== null && props.comment.author.id === authenticatedUser.id && !props.comment.isPublished)),
            action: () => props.switchEditCommentFormDisplay(props.comment.id)
          },{
            icon: 'fa fa-eye-slash',
            label: trans('icap_blog_post_publish', {}, 'icap_blog'),
            displayed: (props.canEdit || props.canModerate) && !props.comment.isPublished,
            action: () => props.publishComment(props.blogId, props.comment.id)
          },{
            icon: 'fa fa-eye',
            label: trans('icap_blog_post_unpublish', {}, 'icap_blog'),
            displayed: (props.canEdit ||  props.canModerate) && props.comment.isPublished,
            action: () => props.unpublishComment(props.blogId, props.comment.id)
          },{
            icon: 'fa fa-fw fa-flag',
            label: trans('icap_blog_comment_report', {}, 'icap_blog'),
            displayed: authenticatedUser !== null,
            action: () => props.reportComment(props.blogId, props.comment.id),
            dangerous: true
          },{
            icon: 'fa fa-fw fa-trash',
            label: t('delete'),
            displayed: props.canEdit || (props.comment.author !== null && authenticatedUser !== null && props.comment.author.id === authenticatedUser.id && !props.comment.isPublished),
            action: () => props.deleteComment(props.blogId, props.comment.id),
            dangerous: true
          }
        ]}
      />
    }
  </div>

CommentComponent.propTypes = {
  comment: T.object,
  showEditCommentForm: T.string,
  canEdit: T.bool,
  showEdit: T.bool,
  showGoToPost: T.bool,
  canModerate: T.bool,
  blogId: T.string,
  switchEditCommentFormDisplay: T.func,
  publishComment: T.func.isRequired,
  unpublishComment: T.func.isRequired,
  deleteComment: T.func.isRequired,
  reportComment: T.func.isRequired,
  editComment: T.func.isRequired
}

CommentComponent.defaultProps = {
  showEdit: true,
  showGoToPost: false
}

const Comment = connect(
  state => ({
    blogId: selectors.blog(state).data.id,
    showEditCommentForm: selectors.showEditCommentForm(state),
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
    canModerate: hasPermission('moderate', resourceSelect.resourceNode(state))
  }),
  dispatch => ({
    publishComment: (blogId, commentId) => {
      dispatch(commentActions.publishComment(blogId, commentId))
    },
    unpublishComment: (blogId, commentId) => {
      dispatch(commentActions.unpublishComment(blogId, commentId))
    },
    reportComment: (blogId, commentId) => {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        title: trans('comment_report_confirm_title', {}, 'icap_blog'),
        question: trans('comment_report_confirm_message', {}, 'icap_blog'),
        handleConfirm: () => dispatch(commentActions.reportComment(blogId, commentId))
      }))
    },
    deleteComment: (blogId, commentId) => {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        title: trans('comment_deletion_confirm_title', {}, 'icap_blog'),
        question: trans('comment_deletion_confirm_message', {}, 'icap_blog'),
        handleConfirm: () => dispatch(commentActions.deleteComment(blogId, commentId))
      }))
    },
    editComment: (blogId, commentId, comment) => {
      dispatch(commentActions.editComment(blogId, commentId, comment))
    },
    switchEditCommentFormDisplay: (val) => {
      dispatch(commentActions.showEditCommentForm(val))
    }
  })
)(CommentComponent)

const CommentCard = props =>
  <Comment
    {...props}
    comment={props.data}
  />


export {Comment, CommentCard}
