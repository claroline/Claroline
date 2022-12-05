import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {selectors as resourceSelect} from '#/main/core/resource/store'
import {UserMessage} from '#/main/core/user/message/components/user-message'
import {UserMessageForm} from '#/main/core/user/message/components/user-message-form'

import {actions as commentActions} from '#/plugin/blog/resources/blog/comment/store'
import {selectors} from '#/plugin/blog/resources/blog/store'

const CommentComponent = (props) =>
  <div key={`comment-container-${props.comment.id}`} className={classes({'unpublished': !props.comment.isPublished}, 'comment')}>
    {!isEmpty(props.showEditCommentForm) && props.showEditCommentForm === props.comment.id ?
      <UserMessageForm
        key={`comment-${props.comment.id}`}
        user={props.comment.author ? props.comment.author : undefined}
        content={props.comment.message}
        allowHtml={true}
        submitLabel={trans('add_comment')}
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
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-pencil',
            label: trans('edit', {}, 'actions'),
            displayed: props.showEdit && (props.canEdit || (props.comment.author !== null && props.currentUser !== null && props.comment.author.id === props.currentUser.id && !props.comment.isPublished)),
            callback: () => props.switchEditCommentFormDisplay(props.comment.id)
          },{
            type: CALLBACK_BUTTON,
            icon: 'fa fa-eye-slash',
            label: trans('icap_blog_post_publish', {}, 'icap_blog'),
            displayed: (props.canEdit || props.canModerate) && !props.comment.isPublished,
            callback: () => props.publishComment(props.blogId, props.comment.id)
          },{
            type: CALLBACK_BUTTON,
            icon: 'fa fa-eye',
            label: trans('icap_blog_post_unpublish', {}, 'icap_blog'),
            displayed: (props.canEdit ||  props.canModerate) && props.comment.isPublished,
            callback: () => props.unpublishComment(props.blogId, props.comment.id)
          },{
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-flag',
            label: trans('icap_blog_comment_report', {}, 'icap_blog'),
            displayed: props.currentUser !== null,
            callback: () => props.reportComment(props.blogId, props.comment.id),
            dangerous: true,
            confirm: {
              title: trans('comment_report_confirm_title', {}, 'icap_blog'),
              message: trans('comment_report_confirm_message', {}, 'icap_blog')
            }
          },{
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-trash',
            label: trans('delete', {}, 'actions'),
            displayed: props.canEdit || (props.comment.author !== null && props.currentUser !== null && props.comment.author.id === props.currentUser.id && !props.comment.isPublished),
            callback: () => props.deleteComment(props.blogId, props.comment.id),
            dangerous: true,
            confirm: {
              title: trans('comment_deletion_confirm_title', {}, 'icap_blog'),
              message: trans('comment_deletion_confirm_message', {}, 'icap_blog')
            }
          }
        ]}
      />
    }
  </div>

CommentComponent.propTypes = {
  currentUser: T.object,
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
    currentUser: securitySelectors.currentUser(state),
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
      dispatch(commentActions.reportComment(blogId, commentId))
    },
    deleteComment: (blogId, commentId) => {
      dispatch(commentActions.deleteComment(blogId, commentId))
    },
    editComment: (blogId, commentId, comment) => {
      dispatch(commentActions.editComment(blogId, commentId, comment))
    },
    switchEditCommentFormDisplay: (val) => {
      dispatch(commentActions.showEditCommentForm(val))
    }
  })
)(CommentComponent)

// TODO : move
const CommentCard = props =>
  <Comment
    {...props}
    comment={props.data}
  />

export {
  Comment,
  CommentCard
}
