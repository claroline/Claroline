import {makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store/reducer'
import {makeFetchReducer} from '#/main/app/api/fetch'
import {makeInstanceAction} from '#/main/app/store/actions'
import {API_FETCH_INVALIDATE} from '#/main/app/api/fetch/store/actions'

import {selectors} from '#/plugin/agenda/events/event/store/selectors'

const reducer = makeFetchReducer(selectors.STORE_NAME, {}, {
  participants: makeListReducer(selectors.LIST_NAME, {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(API_FETCH_INVALIDATE, selectors.STORE_NAME)]: () => true
    })
  })
})

export {
  reducer
}
