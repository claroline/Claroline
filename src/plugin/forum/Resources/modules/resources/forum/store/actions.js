import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST, url} from '#/main/app/api'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/forum/resources/forum/store/selectors'

export const LAST_MESSAGES_LOAD = 'LAST_MESSAGES_LOAD'
export const USER_NOTIFIED = 'USER_NOTIFIED'
export const USER_NOT_NOTIFIED = 'USER_NOT_NOTIFIED'
export const actions = {}

actions.loadLastMessages = makeActionCreator(LAST_MESSAGES_LOAD, 'messages')
actions.fetchLastMessages = (forum) => ({
  [API_REQUEST]: {
    url: url(['apiv2_forum_list_messages', {id: forum.id}])+'?limit='+forum.display.lastMessagesCount+'&sortBy=-id',
    success: (data, dispatch) => {
      dispatch(actions.loadLastMessages(data))
    }
  }
})

actions.validatePost = (message, subjectId, formName) => ({
  [API_REQUEST]: {
    url: ['apiv2_forum_subject_message_update', {message: message.id, subject: subjectId}],
    request: {
      body: JSON.stringify(Object.assign({}, message, {meta: {moderation:'NONE'}})),
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.'+formName))
    }
  }
})

actions.validateSubject = (subject, formName) => ({
  [API_REQUEST]: {
    url: ['apiv2_forum_subject_update', {id: subject.id}],
    request: {
      body: JSON.stringify(Object.assign({}, subject, {meta: {moderation:'NONE'}})),
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.'+formName))
    }
  }
})

actions.unLockUser = (userId, forumId) => ({
  [API_REQUEST]: {
    url: ['claroline_forum_forum_unlock', {user: userId, forum: forumId}],
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.moderation.blockedMessages'))
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.moderation.blockedSubjects'))
    }
  }
})

actions.banUser = (userId, forumId) => ({
  [API_REQUEST]: {
    url: ['claroline_forum_forum_ban', {user: userId, forum: forumId}],
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.moderation.blockedMessages'))
    }
  }
})

actions.notified = makeActionCreator(USER_NOTIFIED)
actions.notify = (forum, user) => ({
  [API_REQUEST]: {
    url: ['claroline_forum_forum_notify', {user: user.id, forum: forum.id}],
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(actions.notified())
    }
  }
})

actions.notNotified = makeActionCreator(USER_NOT_NOTIFIED)
actions.stopNotify = (forum, user) => ({
  [API_REQUEST]: {
    url: ['claroline_forum_forum_unnotify', {user: user.id, forum: forum.id}],
    request: {
      method: 'PATCH'
    },
    success: (data, dispatch) => {
      dispatch(actions.notNotified())
    }
  }
})
