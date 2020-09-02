import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  MEETINGS_UPDATE,
  MEETINGS_SET_LOADED
} from '#/integration/big-blue-button/integration/bbb/store/actions'

const reducer = combineReducers({
  loaded: makeReducer(false, {
    [MEETINGS_SET_LOADED]: (state, action) => action.loaded,
    [MEETINGS_UPDATE]: () => true
  }),
  maxMeetings: makeReducer(0, {
    [MEETINGS_UPDATE]: (state, action) => action.maxMeetings
  }),
  maxParticipants: makeReducer(0, {
    [MEETINGS_UPDATE]: (state, action) => action.maxParticipants
  }),
  maxMeetingParticipants: makeReducer(0, {
    [MEETINGS_UPDATE]: (state, action) => action.maxMeetingParticipants
  }),
  activeMeetingsCount: makeReducer(0, {
    [MEETINGS_UPDATE]: (state, action) => action.activeMeetingsCount
  }),
  participantsCount: makeReducer(0, {
    [MEETINGS_UPDATE]: (state, action) => action.participantsCount
  }),
  meetings: makeReducer([], {
    [MEETINGS_UPDATE]: (state, action) => action.meetings || []
  })
})

export {
  reducer
}