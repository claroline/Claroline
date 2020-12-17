import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/exo/tools/bank/store/selectors'

const reducer = combineReducers({
  questions: makeListReducer(selectors.LIST_QUESTIONS)
})

export {
  reducer
}