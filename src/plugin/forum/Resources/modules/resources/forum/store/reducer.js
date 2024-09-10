import cloneDeep from 'lodash/cloneDeep'

import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {RESOURCE_LOAD} from '#/main/core/resource/store'

import {USER_NOTIFIED, USER_NOT_NOTIFIED} from '#/plugin/forum/resources/forum/store/actions'
import {reducer as playerReducer} from '#/plugin/forum/resources/forum/player/store/reducer'
import {reducer as moderationReducer} from '#/plugin/forum/resources/forum/moderation/store/reducer'
import {reducer as overviewReducer} from '#/plugin/forum/resources/forum/overview/store/reducer'
import {selectors} from '#/plugin/forum/resources/forum/store/selectors'

const reducer = combineReducers({
  forum: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.forum,
    [FORM_SUBMIT_SUCCESS+'/'+selectors.STORE_NAME+'.forumForm']: (state, action) => action.updatedData
  }),
  tags: makeReducer([], {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.tags
  }),
  usersCount: makeReducer(0, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.users
  }),
  subjectsCount: makeReducer(0, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.subjects
  }),
  messagesCount: makeReducer(0, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.messages
  }),
  isValidatedUser: makeReducer(false, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.isValidatedUser
  }),
  banned: makeReducer(false, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.banned
  }),
  notified: makeReducer(false, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.notified,
    [USER_NOTIFIED]: () => true,
    [USER_NOT_NOTIFIED]: () => false
  }),
  myMessages:  makeReducer(0, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.myMessages
  }),
  lastMessages: overviewReducer,
  moderation: moderationReducer,
  subjects: playerReducer
})

export {
  reducer
}
