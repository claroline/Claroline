import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store'

const reducer = combineReducers({
  resource: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'youtube_video')]: (state, action) => action.resourceData.resource,
  }),
  progression: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'youtube_video')]: (state, action) => action.resourceData.userEvaluation.progression
  })
})

export {
  reducer
}
