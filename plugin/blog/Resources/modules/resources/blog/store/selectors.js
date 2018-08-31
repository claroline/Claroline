import {createSelector} from 'reselect'

const STORE_NAME = 'resource'

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

const user = createSelector(
  [resource],
  (resource) => resource.user
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

const pdfEnabled = createSelector(
  [resource],
  (resource) => resource.pdfEnabled
)

const postEdit = createSelector(
  [resource],
  (resource) => resource.post_edit
)

const goHome = createSelector(
  [resource],
  (resource) => resource.goHome
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

export const selectors = {
  countTags,
  displayTagsFrequency,
  blog,
  mode,
  user,
  post,
  trustedUsers,
  showEditCommentForm,
  showCommentForm,
  showComments,
  comments,
  posts,
  pdfEnabled,
  postEdit,
  goHome,
  calendarSelectedDate,
  STORE_NAME
}
