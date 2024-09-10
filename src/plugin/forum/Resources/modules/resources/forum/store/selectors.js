import {createSelector} from 'reselect'

import {hasPermission} from '#/main/app/security'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

const STORE_NAME = 'claroline_forum'

const resource = (state) => state[STORE_NAME]

const forum = createSelector(
  [resource],
  (resource) => resource.forum
)

const isValidatedUser = createSelector(
  [resource],
  (resource) => resource.isValidatedUser
)

const subjects = createSelector(
  [resource],
  (resource) => resource.subjects
)

const messages = createSelector(
  [subjects],
  (subjects) => subjects.messages
)

const currentPage = createSelector(
  [messages],
  (messages) => messages.currentPage
)
const totalResults = createSelector(
  [messages],
  (messages) => messages.totalResults
)
const sortOrder = createSelector(
  [messages],
  (messages) => messages.sortOrder
)

const lastMessages = createSelector(
  [resource],
  (resource) => resource.lastMessages
)

const bannedUser = createSelector(
  [resource],
  (resource) => resource.banned
)
const moderator = createSelector(
  [resourceSelectors.resourceNode],
  (resourceNode) => hasPermission('edit', resourceNode)
)

const notified = createSelector(
  [resource],
  (resource) => resource.notified
)

const myMessages = createSelector(
  [resource],
  (resource) => resource.myMessages
)

const subject = createSelector(
  [subjects],
  (subjects) => subjects.current
)

const editingSubject = createSelector(
  [subjects],
  (subjects) => subjects.form.editingSubject
)
const closedSubject = createSelector(
  [subject],
  (subject) => subject.meta.closed
)
const showSubjectForm = createSelector(
  [subjects],
  (subjects) => subjects.form.showSubjectForm
)

const visibleMessages = createSelector(
  [messages],
  (messages) => messages.data.filter(message => 'NONE' === message.meta.moderation)
)
const moderatedMessages = createSelector(
  [messages],
  (messages) => messages.data.filter(message => 'NONE' !== message.meta.moderation)
)
const tags = createSelector(
  [resource],
  (resource) => resource.tags || []
)
const tagsCount = createSelector(
  [tags],
  (tags) => tags.reduce((obj, tag) => {
    if (!obj[tag.name]) {
      obj[tag.name] = 0
    }
    obj[tag.name]++

    return obj
  }, {})
)
const usersCount = createSelector(
  [resource],
  (resource) => resource.usersCount
)

const subjectsCount = createSelector(
  [resource],
  (resource) => resource.subjectsCount
)

const messagesCount = createSelector(
  [resource],
  (resource) => resource.messagesCount
)

export const selectors = {
  STORE_NAME,
  resource,
  forum,
  isValidatedUser,
  subject,
  messages,
  totalResults,
  currentPage,
  sortOrder,
  bannedUser,
  moderator,
  notified,
  showSubjectForm,
  editingSubject,
  closedSubject,
  visibleMessages,
  moderatedMessages,
  tags,
  tagsCount,
  usersCount,
  subjectsCount,
  messagesCount,
  lastMessages,
  myMessages
}
