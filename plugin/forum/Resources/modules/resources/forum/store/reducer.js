import cloneDeep from 'lodash/cloneDeep'

import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {USER_NOTIFIED, USER_NOT_NOTIFIED} from '#/plugin/forum/resources/forum/store/actions'
import {reducer as editorReducer} from '#/plugin/forum/resources/forum/editor/store/reducer'
import {reducer as playerReducer} from '#/plugin/forum/resources/forum/player/store/reducer'
import {reducer as moderationReducer} from '#/plugin/forum/resources/forum/moderation/store/reducer'
import {reducer as overviewReducer} from '#/plugin/forum/resources/forum/overview/store/reducer'
import {select} from '#/plugin/forum/resources/forum/store/selectors'

const reducer = combineReducers({
  forum: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.forum,
    [FORM_SUBMIT_SUCCESS+'/'+select.STORE_NAME+'.forumForm']: (state, action) => action.updatedData,
    [USER_NOTIFIED]: (state) => {
      const newState = cloneDeep(state)
      newState.meta.notified = true
      return newState
    },
    [USER_NOT_NOTIFIED]: (state) => {
      const newState = cloneDeep(state)
      newState.meta.notified = false
      return newState
    }
  }),
  lastMessages: overviewReducer,
  moderation: moderationReducer,
  forumForm: editorReducer,
  subjects: playerReducer
})

export {
  reducer
}
