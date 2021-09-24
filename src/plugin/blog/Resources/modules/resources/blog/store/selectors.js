import {createSelector} from 'reselect'

import {hasPermission} from '#/main/app/security'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

const STORE_NAME = 'icap_blog'

const resource = (state) => state[STORE_NAME]

const blog = createSelector(
  [resource],
  (resource) => resource.blog
)

const trustedUsers = createSelector(
  [resource],
  (resource) => resource.trustedUsers
)

const mode = createSelector(
  [resource],
  (resource) => resource.mode
)

const showEditCommentForm = createSelector(
  [resource],
  (resource) => resource.showEditCommentForm
)

const showCommentForm = createSelector(
  [resource],
  (resource) => resource.showCommentForm
)

const showComments = createSelector(
  [resource],
  (resource) => resource.showComments
)

const comments = createSelector(
  [resource],
  (resource) => resource.comments
)

const posts = createSelector(
  [resource],
  (resource) => {
    return resource.posts
  }
)

const postEdit = createSelector(
  [resource],
  (resource) => resource.post_edit
)

const post = createSelector(
  [resource],
  (resource) => resource.post
)

const calendarSelectedDate = createSelector(
  [resource],
  (resource) => resource.calendarSelectedDate
)

const countTags = createSelector(
  [blog],
  (blog) => blog.data.tags.reduce((obj, tag) => {
    if (!obj[tag]) {
      obj[tag] = 0
    }
    obj[tag]++
    return obj
  }, {})
)

const displayTagsFrequency = createSelector(
  [blog],
  (blog) => {
    let obj = {}
    Object.keys(blog.data.tags).map(function (keyName) {
      let value = blog.data.tags[keyName]
      obj[keyName + '(' + value + ')'] = value
    })

    return obj
  }
)

const countPostComments = createSelector(
  [resourceSelectors.resourceNode, post],
  (resourceNode, post) => {
    if (hasPermission('edit', resourceNode)) {
      return post.commentsNumber + post.commentsNumberUnpublished
    }

    return post.commentsNumber
  }
)

export const selectors = {
  STORE_NAME,
  countTags,
  displayTagsFrequency,
  blog,
  mode,
  post,
  trustedUsers,
  showEditCommentForm,
  showCommentForm,
  showComments,
  comments,
  posts,
  postEdit,
  calendarSelectedDate,
  countPostComments
}
