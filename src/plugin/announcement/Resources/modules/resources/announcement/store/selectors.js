import {createSelector} from 'reselect'

const STORE_NAME = 'claroline_announcement_aggregate'
const resource = (state) => state[STORE_NAME]

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

const sortedPosts = createSelector(
  [posts],
  (posts) => posts.slice().sort((a, b) => {
    if (null === a.meta.publishedAt || a.meta.publishedAt < b.meta.publishedAt) {
      return 1
    } else if (null === b.meta.publishedAt || a.meta.publishedAt > b.meta.publishedAt) {
      return -1
    }

    return 0
  })
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
  sortedPosts,
  announcement,
  detail,
  workspaceRoles
}
