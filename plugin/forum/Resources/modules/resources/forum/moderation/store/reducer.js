import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeInstanceAction} from '#/main/app/store/actions'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/forum/resources/forum/store/selectors'

const reducer = combineReducers({
  flaggedMessages: makeListReducer(`${selectors.STORE_NAME}.moderation.flaggedMessages`, {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  flaggedSubjects: makeListReducer(`${selectors.STORE_NAME}.moderation.flaggedSubjects`, {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  blockedMessages: makeListReducer(`${selectors.STORE_NAME}.moderation.blockedMessages`, {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  blockedSubjects: makeListReducer(`${selectors.STORE_NAME}.moderation.blockedSubjects`, {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: () => true
    })
  })
})


export {
  reducer
}
