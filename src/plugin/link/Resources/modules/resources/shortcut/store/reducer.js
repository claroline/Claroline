import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'
import {FORM_SUBMIT_SUCCESS, makeFormReducer} from '#/main/app/content/form/store'

import {selectors} from '#/plugin/link/resources/shortcut/store/selectors'

const reducer = combineReducers({
  shortcut: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, 'shortcut')]: (state, action) => action.resourceData.shortcut || state,
    [makeInstanceAction(FORM_SUBMIT_SUCCESS, selectors.FORM_NAME)]: (state, action) => action.updatedData
  }),
  form: makeFormReducer(selectors.FORM_NAME, {}, {
    originalData: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, 'shortcut')]: (state, action) => action.resourceData.shortcut || state
    }),
    data: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, 'shortcut')]: (state, action) => action.resourceData.shortcut || state
    })
  })
})

export {
  reducer
}
