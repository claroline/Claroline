import React from 'react'
import {connect} from 'react-redux'
import classes from 'classnames'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'
import {trans, transChoice} from '#/main/core/translation'
import {displayDate} from '#/main/core/scaffolding/date'
import {UserAvatar} from '#/main/core/user/components/avatar.jsx'
import {hasPermission} from '#/main/core/resource/permissions'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {actions as listActions} from '#/main/core/data/list/actions'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {UrlButton} from '#/main/app/button/components/url'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {Button} from '#/main/app/action/components/button'
import {PostType} from '#/plugin/blog/resources/blog/post/components/prop-types'
import {actions as postActions} from '#/plugin/blog/resources/blog/post/store'
import {Comments} from '#/plugin/blog/resources/blog/comment/components/comments'
import {getCommentsNumber, splitArray} from '#/plugin/blog/resources/blog/utils.js'
import ButtonToolbar from 'react-bootstrap/lib/ButtonToolbar'

const PostComponent = props =>
  <div className='data-card-blog'>
    {props.post.id &&
      <div>
        <div className="post-container">
          <div className={classes('post-header', {'unpublished': !props.post.isPublished})}>
            <h2 className={'post-title'}>
              <a href={`#/${props.post.slug}`}>{props.post.title}</a>
            </h2>
            <InfoBar
              displayViews={props.displayViews}
              blogId={props.blogId}
              getPostsByAuthor={props.getPostsByAuthor}
              post={props.post}
              key={`data-card-subtitle-${props.post.id}`} />
            <ActionBar
              blogId={props.blogId}
              post={props.post}
              canEdit={props.canEdit}
              publishPost={props.publishPost}
              pinPost={props.pinPost}
              deletePost={props.deletePost}
            />
          </div>
          {'sm' !== props.size && props.post.content &&
            <div key="data-card-description" className="post-content">
              <HtmlText>{props.post.content}</HtmlText>
              {props.post.abstract &&
                <div>
                  ...
                  <div className="read_more">
                    <a href={`#/${props.post.slug}`}>{trans('read_more', {}, 'icap_blog')}</a> <span className="fa fa-long-arrow-right"></span>
                  </div>
                </div>
              }
            </div>
          }
          {'sm' !== props.size &&
            <div key="data-card-footer" className="data-card-footer">
              <Footer
                {...props}
                post={props.post}
                canEdit={props.canEdit} />
            </div>
          }
          {props.full &&
            <div className="post-content">
              <Comments
                blogId={props.blogId}
                postId={props.post.id}
                canComment={props.canComment}
                showForm={props.showCommentForm}
                opened={props.showComments}
                comments={props.post.comments}
              />
            </div>
          }
        </div>
      </div>
    }
  </div>
    
PostComponent.propTypes = {
  canEdit: T.bool,
  full: T.bool,
  blogId: T.string.isRequired,
  size: T.string,
  canComment: T.bool,
  displayViews: T.bool,
  orientation: T.string,
  showCommentForm: T.bool,
  showComments: T.bool,
  post: T.shape(PostType.propTypes).isRequired,
  getPostsByAuthor: T.func.isRequired,
  publishPost: T.func.isRequired,
  pinPost: T.func.isRequired,
  deletePost: T.func.isRequired
}

const PostCard = props =>
  <PostComponent 
    {...props} 
    post={props.data}
  />

PostCard.propTypes = {
  data: T.shape(PostType.propTypes)
}

const InfoBar = props =>
  <ul className="list-inline post-infos">
    <li 
      onClick={(e) => {
        props.getPostsByAuthor(props.blogId, props.post.author.firstName + ' ' + props.post.author.lastName)
        e.preventDefault()
        e.stopPropagation()
      }}>
      <span>
        <UrlButton target={['claro_user_profile', {publicUrl: get(props.post.author, 'meta.publicUrl')}]}>
          <UserAvatar className="user-picture" picture={props.post.author ? props.post.author.picture : undefined} alt={true} />
        </UrlButton>
        <a className="user-name">{props.post.author.firstName} {props.post.author.lastName}</a>
      </span>
    </li>
    <li><span className="fa fa-calendar"></span> {displayDate(props.post.publicationDate, false, true)} </li>
    {props.displayViews &&
      <li><span className="fa fa-eye"></span> {transChoice('display_views', props.post.viewCounter, {'%count%': props.post.viewCounter}, 'platform')}</li> 
    }
    {props.post.pinned &&
      <li><span className="label label-success">{trans('icap_blog_post_pinned', {}, 'icap_blog')}</span></li>
    }
  </ul>
    
InfoBar.propTypes = {
  getPostsByAuthor: T.func.isRequired,
  blogId: T.string.isRequired,
  post: T.shape(PostType.propTypes),
  displayViews: T.bool
}

const ActionBar = props =>
  <ButtonToolbar className="post-actions">
    <Button
      id={`action-edit-${props.post.id}`}
      type="link"
      icon="fa fa-pencil"
      className="btn btn-link"
      tooltip="top"
      label={trans('edit_post_short', {}, 'icap_blog')}
      title={trans('edit_post_short', {}, 'icap_blog')}
      target={`/${props.post.slug}/edit`}
    />
    <Button
      id={`action-publish-${props.post.id}`}
      type="callback"
      icon={props.post.isPublished ? 'fa fa-eye' : 'fa fa-eye-slash'}
      className="btn btn-link"
      tooltip="top"
      label={props.post.isPublished ? trans('icap_blog_post_unpublish', {}, 'icap_blog') : trans('icap_blog_post_publish', {}, 'icap_blog')}
      title={props.post.isPublished ? trans('icap_blog_post_unpublish', {}, 'icap_blog') : trans('icap_blog_post_publish', {}, 'icap_blog')}
      callback={() => props.publishPost(props.blogId, props.post.id)}
    />
    <Button
      id={`action-pin-${props.post.id}`}
      type="callback"
      icon={props.post.pinned ? 'fa fa-thumb-tack' : 'fa fa-thumb-tack fa-rotate-90'}
      className="btn btn-link"
      tooltip="top"
      label={props.post.pinned ? trans('icap_blog_post_unpin', {}, 'icap_blog') : trans('icap_blog_post_pin', {}, 'icap_blog')}
      title={props.post.pinned ? trans('icap_blog_post_unpin', {}, 'icap_blog') : trans('icap_blog_post_pin', {}, 'icap_blog')}
      callback={() => props.pinPost(props.blogId, props.post.id)}
    />
    <Button
      id={`action-delete-${props.post.id}`}
      type="callback"
      icon="fa fa-trash"
      className="btn btn-link"
      tooltip="top"
      label={trans('delete', {}, 'platform')}
      title={trans('delete', {}, 'platform')}
      dangerous={true}
      callback={() => props.deletePost(props.blogId, props.post.id, props.post.title)}
    />
  </ButtonToolbar>

ActionBar.propTypes = {
  canEdit:T.bool,
  post: T.shape(PostType.propTypes),
  blogId: T.string.isRequired,
  publishPost: T.func.isRequired,
  deletePost: T.func.isRequired,
  pinPost: T.func.isRequired
}
    
const Footer = props =>
  <div>
    <ul className='list-inline post-tags pull-left'>
      <li><span className="fa fa-tags"></span></li>
      {!isEmpty(props.post.tags) ? (
        splitArray(props.post.tags).map((tag, index) =>(
          <li key={index}>
            <a href="#" onClick={() => {
              props.getPostsByTag(tag)
            }}>{tag}</a>
          </li>
        ))
      ) : (
        trans('no_tags', {}, 'icap_blog')
      )}
    </ul>
    <ul className='list-inline pull-right'>
      <li><span className="fa fa-comments"></span></li>
      <li>
        {getCommentsNumber(props.canEdit, props.post.commentsNumber, props.post.commentsNumberUnpublished) > 0
          ? transChoice('comments_number', getCommentsNumber(props.canEdit, props.post.commentsNumber, props.post.commentsNumberUnpublished), 
            {'%count%': getCommentsNumber(props.canEdit, props.post.commentsNumber, props.post.commentsNumberUnpublished)}, 'icap_blog')
          : trans('no_comment', {}, 'icap_blog')}
        {props.canEdit && props.post.commentsNumberUnpublished 
          ? transChoice('comments_pending', props.post.commentsNumberUnpublished, {'%count%': props.post.commentsNumberUnpublished}, 'icap_blog')
          : ''}
      </li>
    </ul>
  </div>
        
Footer.propTypes = {
  canEdit:T.bool,
  canComment:T.bool,
  displayViews:T.bool,
  getPostsByTag:T.func.isRequired,
  post: T.shape(PostType.propTypes)
}    

const PostCardContainer = connect(
  state => ({
    blogId: state.blog.data.id,
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
    canComment: state.blog.data.options.data.authorizeComment,
    displayViews: state.blog.data.options.data.displayPostViewCounter
  }),
  dispatch => ({
    publishPost: (blogId, postId) => {
      dispatch(postActions.publishPost(blogId, postId))
    },
    pinPost: (blogId, postId) => {
      dispatch(postActions.pinPost(blogId, postId))
    },
    deletePost: (blogId, postId, postName) => {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        title: trans('post_deletion_confirm_title', {}, 'icap_blog'),
        question: trans('post_deletion_confirm_message', {'postName': postName}, 'icap_blog'),
        handleConfirm: () => dispatch(postActions.deletePost(blogId, postId))
      }))
    },
    getPostsByAuthor: (blogId, authorName) => {
      dispatch(listActions.addFilter('posts', 'authorName', authorName))
      dispatch(postActions.initDataList())
    },
    getPostsByTag: (tag) => {
      dispatch(listActions.addFilter('posts', 'tags', tag))
      dispatch(postActions.initDataList())
    }
  })
)(PostCard)
  
const PostContainer = connect(
  state => ({
    data: state.post,
    full: true
  })
)(PostCardContainer)
    
export {PostCardContainer as PostCard, PostContainer as Post}