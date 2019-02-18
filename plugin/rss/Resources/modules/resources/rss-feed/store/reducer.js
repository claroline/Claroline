import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors as editorSelectors, reducer as editorReducer} from '#/plugin/rss/resources/rss-feed/editor/store'

const reducer = combineReducers(Object.assign({
  rssFeed: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.rssFeed || state,
    [FORM_SUBMIT_SUCCESS+'/'+editorSelectors.FORM_NAME]: (state, action) => action.updatedData
  })
}, editorReducer))

export {
  reducer
}
