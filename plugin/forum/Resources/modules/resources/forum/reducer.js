import cloneDeep from 'lodash/cloneDeep'

import {makeReducer} from '#/main/app/store/reducer'


import {reducer as editorReducer} from '#/plugin/forum/resources/forum/editor/reducer'
import {reducer as playerReducer} from '#/plugin/forum/resources/forum/player/reducer'
import {reducer as moderationReducer} from '#/plugin/forum/resources/forum/moderation/reducer'
import {reducer as overviewReducer} from '#/plugin/forum/resources/forum/overview/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'
import {USER_NOTIFIED, USER_NOT_NOTIFIED} from '#/plugin/forum/resources/forum/actions'
const reducer = {
  forum: makeReducer({}, {
    [FORM_SUBMIT_SUCCESS+'/forumForm']: (state, action) => action.updatedData,
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
}

export {
  reducer
}
