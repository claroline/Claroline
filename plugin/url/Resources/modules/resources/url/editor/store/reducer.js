import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors as urlSelectors} from '#/plugin/url/resources/url/store/selectors'
import {selectors} from '#/plugin/url/resources/url/editor/store/selectors'

const reducer = combineReducers({
  urlForm: makeFormReducer(selectors.FORM_NAME, {}, {
    data: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, urlSelectors.STORE_NAME)]: (state, action) => action.resourceData.url
    }),
    initialData: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, urlSelectors.STORE_NAME)]: (state, action) => action.resourceData.url
    })
  }),
  availablePlaceholders: makeReducer([], {
    [makeInstanceAction(RESOURCE_LOAD, urlSelectors.STORE_NAME)]: (state, action) => action.resourceData.placeholders
  })
})

export {
  reducer
}
