import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeInstanceAction} from '#/main/app/store/actions'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors as baseSelectors} from '#/plugin/exo/resources/quiz/store/selectors'
import {selectors} from '#/plugin/exo/resources/quiz/papers/store/selectors'
import {PAPER_CURRENT, PAPER_ADD} from '#/plugin/exo/resources/quiz/papers/store/actions'

const reducer = combineReducers({
  list: makeListReducer(selectors.LIST_NAME, {}, {
    invalidated: makeReducer(false, {
      [PAPER_ADD]: () => true,
      [makeInstanceAction(RESOURCE_LOAD, baseSelectors.STORE_NAME)]: () => true
    })
  }),
  current: makeReducer(null, {
    [PAPER_CURRENT]: (state, action) => action.paper
  })
})

export {
  reducer
}
