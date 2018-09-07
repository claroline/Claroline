import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {currentUser} from '#/main/core/user/current'
import {UserMessageForm} from '#/main/core/user/message/components/user-message-form'
import {t, trans} from '#/main/core/translation'
import isEmpty from 'lodash/isEmpty'
import {CommentCard} from '#/plugin/blog/resources/blog/comment/components/comment'
import {actions as commentActions} from '#/plugin/blog/resources/blog/comment/store'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/core/resource/permissions'
import {constants} from '#/plugin/blog/resources/blog/constants'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {selectors} from '#/plugin/blog/resources/blog/store'

const authenticatedUser = currentUser()

const CommentsComponent = props =>
  <div>
    <section>
      <h4 className="comments-title">
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
          {t(props.opened ? 'hide':'show')}
        </button>
      </h4>

      {props.opened && props.canComment && (authenticatedUser !== null || props.canAnonymousComment) &&
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
              {!props.canModerate && !props.canEdit && props.isModerated &&
                <span className="help-block">
                  <span className="fa fa-fw fa-info-circle"></span>
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
          {!isEmpty(props.comments) &&
            <Button
              icon={'fa fa-3x fa-arrow-circle-up'}
              label={trans('go-up', {}, 'icap_blog')}
              type={CALLBACK_BUTTON}
              tooltip="bottom"
              callback={() => props.goUp()}
              className="btn-link button-go-to-top pull-right"
              target={'/new'}
            />
          }
        </section>
      }
    </section>
  </div>

CommentsComponent.propTypes = {
  switchCommentFormDisplay: T.func.isRequired,
  switchCommentsDisplay: T.func.isRequired,
  goUp: T.func.isRequired,
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
    user: selectors.user(state),
    opened: selectors.showComments(state),
    isModerated: selectors.blog(state).data.options.data.commentModerationMode !== constants.COMMENT_MODERATION_MODE_NONE,
    showForm: selectors.showCommentForm(state),
    showEditCommentForm: selectors.showEditCommentForm(state),
    comments: selectors.comments(state).data,
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
    canModerate: hasPermission('moderate', resourceSelect.resourceNode(state))
  }),
  dispatch => ({
    switchCommentsDisplay: (val) => {
      dispatch(commentActions.showComments(val))
    },
    switchCommentFormDisplay: (val) => {
      dispatch(commentActions.showCommentForm(val))
    },
    submitComment: (blogId, postId, comment) => {
      dispatch(commentActions.submitComment(blogId, postId, comment))
    },
    switchEditCommentFormDisplay: (val) => {
      dispatch(commentActions.showEditCommentForm(val))
    },
    goUp: () => {
      let node = document.getElementById('blog-top-page')
      if (node) {
        node.scrollIntoView({block: 'end', behavior: 'smooth', inline: 'center'})
      }
    }
  })
)(CommentsComponent)

export {Comments}
