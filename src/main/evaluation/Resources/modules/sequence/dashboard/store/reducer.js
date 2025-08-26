import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {API_FETCH_PENDING} from '#/main/app/api/fetch/store/actions'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/evaluation/sequence/dashboard/store/selectors'

const reducer = combineReducers({
  evaluations: makeListReducer(selectors.STORE_NAME+'.evaluations', {
    sortBy: { property: 'lastActivityAt', direction: -1 }
  }, {
    loaded: makeReducer(false, {
      [makeInstanceAction(API_FETCH_PENDING, 'evaluationSequence')]: () => false
    })
  }),
  logs: makeListReducer(selectors.STORE_NAME + '.logs', {
    sortBy: { property: 'date', direction: -1 }
  }, {
    loaded: makeReducer(false, {
      [makeInstanceAction(API_FETCH_PENDING, 'evaluationSequence')]: () => false
    })
  })
})

export {
  reducer
}
