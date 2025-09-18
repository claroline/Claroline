import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {API_FETCH_PENDING} from '#/main/app/api/fetch/store/actions'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/evaluation/sequence/dashboard/store/selectors'
import {SEQUENCE_RELOAD} from '#/main/evaluation/sequence/store/actions'

const reducer = combineReducers({
  logs: makeListReducer(selectors.STORE_NAME + '.logs', {
    sortBy: { property: 'date', direction: -1 }
  }, {
    loaded: makeReducer(false, {
      [makeInstanceAction(API_FETCH_PENDING, 'evaluationSequence')]: () => false
    }),
    invalidated: makeReducer(false, {
      [SEQUENCE_RELOAD]: () => true
    })
  })
})

export {
  reducer
}
