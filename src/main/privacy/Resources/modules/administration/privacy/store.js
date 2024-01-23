import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {makeFormReducer} from '#/main/app/content/form/store'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

const STORE_NAME = 'privacy'
const FORM_NAME = STORE_NAME+'.parameters'

const store = (state) => state[STORE_NAME]

const parameters = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

const selectors = {
  STORE_NAME,
  store,
  parameters,
  FORM_NAME
}

const reducer = combineReducers({
  parameters: makeFormReducer(selectors.FORM_NAME, {}, {
    originalData: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, 'privacy')]: (state, action) => action.toolData.privacy
    }),
    data: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, 'privacy')]: (state, action) => action.toolData.privacy
    })
  })
})

export {
  reducer,
  selectors
}
