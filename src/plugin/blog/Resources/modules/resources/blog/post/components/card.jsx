import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

// TODO : remove me
import ButtonToolbar from 'react-bootstrap/lib/ButtonToolbar'

import {withRouter} from '#/main/app/router'
import {trans, transChoice} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {hasPermission} from '#/main/app/security'

import {ContentHtml} from '#/main/app/content/components/html'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {LinkButton} from '#/main/app/buttons/link/components/button'
import {route} from '#/main/core/user/routing'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {selectors as resourceSelect} from '#/main/core/resource/store'

import {selectors} from '#/plugin/blog/resources/blog/store'
import {PostType} from '#/plugin/blog/resources/blog/post/components/prop-types'
import {actions as postActions} from '#/plugin/blog/resources/blog/post/store'
import {splitArray} from '#/plugin/blog/resources/blog/utils'
import {updateQueryParameters} from '#/plugin/blog/resources/blog/utils'
import {asset} from '#/main/app/config'

const CardMeta = props =>
  <ul className="list-inline post-infos">
    <li
      onClick={(e) => {
        props.getPostsByAuthor(props.history, props.location.search, props.blogId, props.post.author.firstName + ' ' + props.post.author.lastName)
        e.preventDefault()
        e.stopPropagation()
      }}
    >
      <span>
        <LinkButton target={route(props.post.author || {})}>
          <UserAvatar className="user-picture" picture={props.post.author ? props.post.author.picture : undefined} alt={true} />
        </LinkButton>
        <a className="user-name link">{props.post.author.firstName} {props.post.author.lastName}</a>
      </span>
    </li>

    <li>
      <span className="fa fa-calendar"/> {displayDate(props.post.publicationDate, false, false)}
    </li>

    {props.displayViews &&
      <li>
        <span className="fa fa-eye" />
        {transChoice('display_views', props.post.viewCounter, {'%count%': props.post.viewCounter}, 'platform')}
      </li>
    }

    {props.post.pinned &&
      <li>
        <span className="label label-success">{trans('icap_blog_post_pinned', {}, 'icap_blog')}</span>
      </li>
    }

    {!props.post.isPublished &&
      <li>
        <span className="label label-danger">{props.post.status ? trans('unpublished_date', {}, 'icap_blog') : trans('unpublished', {}, 'icap_blog')}</span>
      </li>
    }
  </ul>

CardMeta.propTypes = {
  getPostsByAuthor: T.func.isRequired,
  blogId: T.string.isRequired,
  post: T.shape(PostType.propTypes),
  displayViews: T.bool,
  history: T.object,
  location: T.object
}

const CardActions = props =>
  <ButtonToolbar className="post-actions">
    {props.canEdit &&
      <Button
        id={`action-edit-${props.post.id}`}
        type={LINK_BUTTON}
        icon="fa fa-pencil"
        className="btn btn-link post-button"
        tooltip="top"
        label={trans('edit_post_short', {}, 'icap_blog')}
        title={trans('edit_post_short', {}, 'icap_blog')}
        target={`${props.path}/${props.post.slug}/edit`}
      />
    }

    {(props.canEdit || props.canModerate) &&
      <Button
        id={`action-publish-${props.post.id}`}
        type={CALLBACK_BUTTON}
        icon={props.post.status ? 'fa fa-eye' : 'fa fa-eye-slash'}
        className="btn btn-link post-button"
        tooltip="top"
        label={props.post.status ? trans('icap_blog_post_unpublish', {}, 'icap_blog') : trans('icap_blog_post_publish', {}, 'icap_blog')}
        title={props.post.status ? trans('icap_blog_post_unpublish', {}, 'icap_blog') : trans('icap_blog_post_publish', {}, 'icap_blog')}
        callback={() => props.publishPost(props.blogId, props.post.id)}
      />
    }

    {props.canEdit &&
      <Button
        id={`action-pin-${props.post.id}`}
        type={CALLBACK_BUTTON}
        icon={props.post.pinned ? 'fa fa-thumb-tack' : 'fa fa-thumb-tack fa-rotate-90'}
        className="btn btn-link post-button"
        tooltip="top"
        label={props.post.pinned ? trans('icap_blog_post_unpin', {}, 'icap_blog') : trans('icap_blog_post_pin', {}, 'icap_blog')}
        title={props.post.pinned ? trans('icap_blog_post_unpin', {}, 'icap_blog') : trans('icap_blog_post_pin', {}, 'icap_blog')}
        callback={() => props.pinPost(props.blogId, props.post.id)}
      />
    }

    {props.canEdit &&
      <Button
        id={`action-delete-${props.post.id}`}
        type={CALLBACK_BUTTON}
        icon="fa fa-trash-o"
        className="btn btn-link post-button"
        tooltip="top"
        label={trans('delete', {}, 'platform')}
        title={trans('delete', {}, 'platform')}
        dangerous={true}
        callback={() => props.deletePost(props.blogId, props.post.id, props.post.title)}
      />
    }
  </ButtonToolbar>

CardActions.propTypes = {
  path: T.string.isRequired,
  canEdit:T.bool,
  canModerate:T.bool,
  post: T.shape(PostType.propTypes),
  blogId: T.string.isRequired,
  publishPost: T.func.isRequired,
  deletePost: T.func.isRequired,
  pinPost: T.func.isRequired
}

const CardFooter = props =>
  <div className="data-card-footer">
    <ul className='list-inline post-tags pull-left'>
      <li>
        <span className="fa fa-tags" />
      </li>

      {!isEmpty(props.post.tags) ? (
        splitArray(props.post.tags).map((tag, index) =>(
          <li key={index}>
            <CallbackButton
              className='link'
              callback={() => props.getPostsByTag(props.history, props.location.search, tag)}
            >
              {tag}
            </CallbackButton>
          </li>
        ))
      ) : (
        trans('no_tags', {}, 'icap_blog')
      )}
    </ul>

    <ul className='list-inline pull-right'>
      <li>
        <span className="fa fa-comments" />
      </li>

      <li>
        <LinkButton target={`${props.path}/${props.post.slug}`}>
          {props.post.commentsNumber > 0 ?
            transChoice('comments_number', props.post.commentsNumber, {'%count%': props.post.commentsNumber}, 'icap_blog') :
            trans('no_comment', {}, 'icap_blog')
          }

          {props.canEdit && props.post.commentsNumberUnpublished ?
            transChoice('comments_pending', props.post.commentsNumberUnpublished, {'%count%': props.post.commentsNumberUnpublished}, 'icap_blog') :
            ''
          }
        </LinkButton>
      </li>
    </ul>
  </div>

CardFooter.propTypes = {
  path: T.string.isRequired,
  canEdit:T.bool,
  getPostsByTag:T.func.isRequired,
  post: T.shape(
    PostType.propTypes
  ).isRequired,
  history: T.object,
  location: T.object
}

const PostCardComponent = props =>
  <div className="data-card data-card-col">
    {props.data.poster &&
      <img className="img-responsive" alt={props.data.title} src={asset(props.data.poster.url)} />
    }

    <div className="post-header">
      <h2 className="post-title">
        <LinkButton target={`${props.path}/${props.data.slug}`}>{props.data.title}</LinkButton>
      </h2>

      <CardMeta
        {...props}
        displayViews={props.displayViews}
        blogId={props.blogId}
        getPostsByAuthor={props.getPostsByAuthor}
        post={props.data}
      />

      <CardActions
        blogId={props.blogId}
        post={props.data}
        canEdit={props.canEdit}
        canModerate={props.canModerate}
        publishPost={props.publishPost}
        pinPost={props.pinPost}
        deletePost={props.deletePost}
        path={props.path}
      />
    </div>

    {'sm' !== props.size && props.data.content &&
      <div className="post-content">
        <ContentHtml>{`${props.data.content}${(props.data.abstract ? '[...]' : '')}`}</ContentHtml>

        {props.data.abstract &&
          <LinkButton target={`${props.path}/${props.data.slug}`} className="btn btn-block">
            {trans('read_more', {}, 'icap_blog')}
          </LinkButton>
        }
      </div>
    }

    {'sm' !== props.size &&
      <CardFooter
        {...props}
        post={props.data}
        canEdit={props.canEdit}
      />
    }
  </div>

PostCardComponent.propTypes = {
  path: T.string.isRequired,
  blogId: T.string.isRequired,
  canEdit: T.bool,
  size: T.string,
  canModerate: T.bool,
  displayViews: T.bool,
  orientation: T.string,
  data: T.shape(
    PostType.propTypes
  ).isRequired,
  getPostsByAuthor: T.func.isRequired,
  publishPost: T.func.isRequired,
  pinPost: T.func.isRequired,
  deletePost: T.func.isRequired
}

const PostCard = withRouter(
  connect(
    (state) => ({
      path: resourceSelect.path(state),
      blogId: selectors.blog(state).data.id,
      canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
      canModerate: hasPermission('moderate', resourceSelect.resourceNode(state)),
      displayViews: selectors.blog(state).data.options.data.displayPostViewCounter,
      commentsLoaded: !selectors.comments(state).invalidated
    }),
    (dispatch) => ({
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
      getPostsByAuthor: (history, querystring, blogId, authorName) => {
        history.push(updateQueryParameters(querystring, 'author', authorName))
      },
      getPostsByTag: (history, querystring, tag) => {
        history.push(updateQueryParameters(querystring, 'tag', tag))
      }
    })
  )(PostCardComponent)
)

export {
  PostCard
}
