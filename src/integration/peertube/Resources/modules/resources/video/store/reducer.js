import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store'
import get from 'lodash/get'

const reducer = combineReducers({
  resource: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'peertube_video')]: (state, action) => action.resourceData.resource
  }),
  progression: makeReducer(null, {
    [makeInstanceAction(RESOURCE_LOAD, 'peertube_video')]: (state, action) => get(action.resourceData, 'userEvaluation.progression', null)
  })
})

export {
  reducer
}
