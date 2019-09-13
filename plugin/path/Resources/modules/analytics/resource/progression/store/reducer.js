import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/path/resources/path/store/selectors'

const reducer = combineReducers({
  evaluations: makeListReducer(selectors.STORE_NAME + '.analytics.evaluations', {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: () => true
    })
  })
})

export {
  reducer
}
