import isEmpty from 'lodash/isEmpty'
import {createSelector} from 'reselect'

const STORE_NAME = 'claroline_announcement_aggregate'

const resource = (state) => state[STORE_NAME]

const pageSize = () => 5

const currentPage = createSelector(
  [resource],
  (resource) => resource.currentPage
)
const sortOrder = createSelector(
  [resource],
  (resource) => resource.sortOrder
)

const announcementForm = createSelector(
  [resource],
  (resource) => resource.announcementForm
)
const formHasPendingChanges = createSelector(
  [announcementForm],
  (announcementForm) => announcementForm.pendingChanges
)
const formIsOpened = createSelector(
  [announcementForm],
  (announcementForm) => !!announcementForm.data
)
const formData = createSelector(
  [announcementForm],
  (announcementForm) => announcementForm.data
)
const formErrors = createSelector(
  [announcementForm],
  (announcementForm) => announcementForm.errors
)
const formValidating = createSelector(
  [announcementForm],
  (announcementForm) => announcementForm.validating
)
const formValid = createSelector(
  [formErrors],
  (formErrors) => isEmpty(formErrors)
)

const announcement = createSelector(
  [resource],
  (resource) => resource.announcement
)

const aggregateId = createSelector(
  [announcement],
  (announcement) => announcement.id
)

const posts = createSelector(
  [resource],
  (resource) => resource.posts
)

const pages = createSelector(
  [posts, pageSize],
  (posts, pageSize) => Math.ceil(posts.length / pageSize)
)

const sortedPosts = createSelector(
  [sortOrder, posts],
  (sortOrder, posts) => posts.slice().sort((a, b) => {
    if (null === a.meta.publishedAt || a.meta.publishedAt < b.meta.publishedAt) {
      return -1*sortOrder
    } else if (null === b.meta.publishedAt || a.meta.publishedAt > b.meta.publishedAt) {
      return 1*sortOrder
    }

    return 0
  })
)

const visibleSortedPosts = createSelector(
  [sortedPosts, pageSize, currentPage, sortOrder],
  (sortedPosts, pageSize, currentPage) => sortedPosts.slice(currentPage*pageSize, currentPage*pageSize+pageSize)
)

const announcementDetail = createSelector(
  [resource],
  (resource) => resource.announcementDetail
)

const detail = createSelector(
  [posts, announcementDetail],
  (posts, announcementDetail) => posts.find(post => post.id === announcementDetail)
)

const workspaceRoles = createSelector(
  [resource],
  (resource) => resource.workspaceRoles
)

export const selectors = {
  STORE_NAME,
  aggregateId,
  posts,
  pageSize,
  currentPage,
  pages,
  sortOrder,
  announcement,
  visibleSortedPosts,
  detail,
  workspaceRoles,

  // form (should be generic)
  formHasPendingChanges,
  formIsOpened,
  formData,
  formErrors,
  formValidating,
  formValid
}
