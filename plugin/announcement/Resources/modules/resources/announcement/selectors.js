import isEmpty from 'lodash/isEmpty'
import {createSelector} from 'reselect'

const pageSize = () => 5
const currentPage = state => state.currentPage
const sortOrder = state => state.sortOrder

const announcementForm = state => state.announcementForm
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

const announcement = state => state.announcement

const aggregateId = createSelector(
  [announcement],
  (announcement) => announcement.id
)

const posts = createSelector(
  [announcement],
  (announcement) => announcement.posts
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

const announcementDetail = state => state.announcementDetail
const detail = createSelector(
  [posts, announcementDetail],
  (posts, announcementDetail) => posts.find(post => post.id === announcementDetail)
)

const workspaceRoles = state => state.workspaceRoles

export const select = {
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
