import {createSelector} from 'reselect'

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

const restrictions = createSelector(
  [forum],
  (forum) => forum.restrictions
)

const bannedUser = createSelector(
  [restrictions],
  (restrictions) => restrictions.banned
)
const moderator = createSelector(
  [restrictions],
  (restrictions) => restrictions.moderator
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

const forumId = createSelector(
  [forum],
  (forum) => forum.id
)

const visibleMessages = createSelector(
  [messages],
  (messages) => messages.data.filter(message => 'NONE' === message.meta.moderation)
)
const moderatedMessages = createSelector(
  [messages],
  (messages) => messages.data.filter(message => 'NONE' !== message.meta.moderation)
)

const tagsCount = createSelector(
  [forum],
  (forum) => forum.meta.tags ? forum.meta.tags.reduce((obj, tag) => {
    if (!obj[tag.name]) {
      obj[tag.name] = 0
    }
    obj[tag.name]++
    return obj
  }, {}) : {}
)

export const selectors = {
  STORE_NAME,
  resource,
  forum,
  isValidatedUser,
  subject,
  messages,
  totalResults,
  forumId,
  currentPage,
  sortOrder,
  bannedUser,
  moderator,
  showSubjectForm,
  editingSubject,
  closedSubject,
  visibleMessages,
  moderatedMessages,
  tagsCount,
  lastMessages,
  myMessages
}
