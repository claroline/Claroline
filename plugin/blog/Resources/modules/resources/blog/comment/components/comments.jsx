import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {currentUser} from '#/main/core/user/current'
import {UserMessageForm} from '#/main/core/user/message/components/user-message-form.jsx'
import {t, trans} from '#/main/core/translation'
import {CommentCard} from '#/plugin/blog/resources/blog/comment/components/comment.jsx'
import {actions as commentActions} from '#/plugin/blog/resources/blog/comment/store'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {constants as listConst} from '#/main/core/data/list/constants'

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
            </div>
          }
          <hr/>
        </section>
      }
      {props.opened  &&
        <section className="comments-section">
          <h4>{trans('all_comments', {}, 'icap_blog')}</h4>
          {/*props.comments.map((comment, commentIndex) =>
            !isEmpty(props.showEditCommentForm) && props.showEditCommentForm === comment.id ?
              <UserMessageForm
                key={`comment-${commentIndex}`}
                user={comment.author}
                content={comment.message}
                allowHtml={true}
                submitLabel={t('add_comment')}
                submit={(commentContent) => props.editComment(props.blogId, props.postId, comment.id, commentContent)}
                cancel={() => props.switchEditCommentFormDisplay('')}
              /> :
              <Comment
                key={commentIndex}
                comment={comment}
                postId={props.postId}
              />
          )*/}

          <DataListContainer
            name="comments"
            fetch={{
              url: ['apiv2_blog_comment_list', {blogId: props.blogId, postId: props.postId}],
              autoload: true
            }}
            open={{
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
            selection={{}}
            card={CommentCard}
            display={{
              available : [listConst.DISPLAY_LIST],
              current: listConst.DISPLAY_LIST
            }}
          />
        </section>
      }
    </section>
  </div>
        
CommentsComponent.propTypes = {
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
  showForm: T.bool,
  comments: T.array,
  commentNumber: T.number
}
        
const Comments = connect(
  state => ({
    user: state.user,
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
    submitComment: (blogId, postId, comment) => {
      dispatch(commentActions.submitComment(blogId, postId, comment))
    },
    switchEditCommentFormDisplay: (val) => {
      dispatch(commentActions.showEditCommentForm(val))
    }
  })
)(CommentsComponent) 
    
export {Comments}