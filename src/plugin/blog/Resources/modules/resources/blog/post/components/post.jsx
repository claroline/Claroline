import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as resourceSelect} from '#/main/core/resource/store'

import {selectors} from '#/plugin/blog/resources/blog/store'
import {PostType} from '#/plugin/blog/resources/blog/post/components/prop-types'
import {PostCard} from '#/plugin/blog/resources/blog/post/components/card'
import {Comments} from '#/plugin/blog/resources/blog/comment/components/comments'

const PostComponent = props =>
  <div className="blog-post">
    {props.post.id &&
      <PostCard
        data={props.post}
      />
    }

    {props.post.id && props.canComment &&
      <Comments
        blogId={props.blogId}
        postId={props.post.id}
        canComment={props.canComment}
        canAnonymousComment={props.canAnonymousComment}
      />
    }
  </div>

PostComponent.propTypes = {
  blogId: T.string.isRequired,
  post: T.shape(
    PostType.propTypes
  ).isRequired,
  canEdit: T.bool.isRequired,
  canComment: T.bool.isRequired,
  canAnonymousComment: T.bool.isRequired
}

const Post = connect(
  (state) => ({
    blogId: selectors.blog(state).data.id, // todo : create selector
    post: selectors.post(state),
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
    canComment: selectors.blog(state).data.options.data.authorizeComment, // todo : create selector
    canAnonymousComment: selectors.blog(state).data.options.data.authorizeAnonymousComment // todo : create selector
  })
)(PostComponent)

export {
  Post
}
