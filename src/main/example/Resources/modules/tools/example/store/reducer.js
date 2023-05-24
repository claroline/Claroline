import {combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store'

import {selectors} from '#/main/example//tools/example/store/selectors'
import {reducer as crudReducer} from '#/main/example//tools/example/crud/store/reducer'

const reducer = combineReducers({
  crud: crudReducer,
  form: makeFormReducer(selectors.FORM_NAME)
})

export {
  reducer
}
