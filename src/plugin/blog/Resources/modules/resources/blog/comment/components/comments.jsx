import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classes from 'classnames'

import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {constants as listConst} from '#/main/app/content/list/constants'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as resourceSelect} from '#/main/core/resource/store'

import {UserMessageForm} from '#/main/core/user/message/components/user-message-form'
import {constants} from '#/plugin/blog/resources/blog/constants'
import {selectors} from '#/plugin/blog/resources/blog/store'
import {CommentCard} from '#/plugin/blog/resources/blog/comment/components/comment'
import {actions as commentActions} from '#/plugin/blog/resources/blog/comment/store'

const CommentsComponent = props =>
  <section className={classes('comments-container', {
    opened: props.opened
  })}>
    <h3 className="comments-title">
      <span className="comments-icon">
        <span className="fa fa-fw fa-comments" />
        <span className="comments-count">{props.commentNumber || '0'}</span>
      </span>

      {trans('comments', {}, 'icap_blog')}

      <button
        type="button"
        className="btn btn-link btn-sm btn-toggle-comments"
        onClick={() => props.switchCommentsDisplay(!props.opened)}
      >
        {trans(props.opened ? 'hide':'show')}
      </button>
    </h3>

    {props.opened && props.canComment && (props.currentUser !== null || props.canAnonymousComment) &&
      <section className="comments-section">
        {!props.showComments && !props.showForm &&
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
              user={props.currentUser !== null ? props.currentUser : {}}
              allowHtml={true}
              submitLabel={trans('add_comment')}
              submit={(comment) => props.submitComment(props.blogId, props.postId, comment)}
              cancel={() => props.switchCommentFormDisplay(false)}
            />
            {!props.canModerate && !props.canEdit && props.isModerated &&
              <span className="help-block">
                <span className="fa fa-fw fa-info-circle" />
                {trans('icap_blog_comment_form_moderation_help', {}, 'icap_blog')}
              </span>
            }
          </div>
        }
        <hr/>
      </section>
    }

    {props.opened  &&
      <section className="comments-section">
        <h4>{trans('all_comments', {}, 'icap_blog')}</h4>

        <ListData
          name={selectors.STORE_NAME + '.comments'}
          fetch={{
            url: ['apiv2_blog_comment_list', {blogId: props.blogId, postId: props.postId}],
            autoload: true
          }}
          definition={[
            {
              name: 'creationDate',
              label: trans('icap_blog_post_form_creationDate', {}, 'icap_blog'),
              type: 'date',
              displayed: true
            },{
              name: 'message',
              label: trans('content', {}, 'platform'),
              type: 'string',
              sortable: false,
              displayed: false
            },{
              name: 'authorName',
              label: trans('author', {}, 'platform'),
              type: 'string'
            }
          ]}
          card={CommentCard}
          display={{
            available : [listConst.DISPLAY_LIST],
            current: listConst.DISPLAY_LIST
          }}
          selectable={false}
        />
      </section>
    }
  </section>

CommentsComponent.propTypes = {
  currentUser: T.object,
  switchCommentFormDisplay: T.func.isRequired,
  switchCommentsDisplay: T.func.isRequired,
  switchEditCommentFormDisplay: T.func.isRequired,
  submitComment: T.func.isRequired,
  blogId: T.string.isRequired,
  postId: T.string.isRequired,
  showEditCommentForm: T.string,
  canComment: T.bool,
  canAnonymousComment: T.bool,
  showComments: T.bool,
  opened: T.bool,
  isModerated: T.bool,
  showForm: T.bool,
  comments: T.array,
  commentNumber: T.number,
  canEdit: T.bool,
  canModerate: T.bool
}

const Comments = connect(
  state => ({
    currentUser: securitySelectors.currentUser(state),
    opened: selectors.showComments(state),
    isModerated: selectors.blog(state).data.options.data.commentModerationMode !== constants.COMMENT_MODERATION_MODE_NONE,
    showForm: selectors.showCommentForm(state),
    showEditCommentForm: selectors.showEditCommentForm(state),
    comments: selectors.comments(state).data,
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
    canModerate: hasPermission('moderate', resourceSelect.resourceNode(state)),
    commentNumber: selectors.countPostComments(state)
  }),
  dispatch => ({
    switchCommentsDisplay(val) {
      dispatch(commentActions.showComments(val))
    },
    switchCommentFormDisplay(val) {
      dispatch(commentActions.showCommentForm(val))
    },
    submitComment(blogId, postId, comment) {
      dispatch(commentActions.submitComment(blogId, postId, comment))
    },
    switchEditCommentFormDisplay(val) {
      dispatch(commentActions.showEditCommentForm(val))
    }
  })
)(CommentsComponent)

export {
  Comments
}
