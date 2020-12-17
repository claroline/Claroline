import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/main/core/resources/text/editor/store/selectors'

const reducer = combineReducers({
  availablePlaceholders: makeReducer([], {
    [makeInstanceAction(RESOURCE_LOAD, 'text')]: (state, action) => action.resourceData.placeholders
  }),
  textForm: makeFormReducer(selectors.FORM_NAME, {}, {
    data: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, 'text')]: (state, action) => action.resourceData.text || state
    }),
    originalData: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, 'text')]: (state, action) => action.resourceData.text || state
    })
  })
})

export {
  reducer
}