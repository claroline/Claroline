import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/rss/resources/rss-feed/store/selectors'
import {selectors as editorSelectors, reducer as editorReducer} from '#/plugin/rss/resources/rss-feed/editor/store'

const reducer = combineReducers(Object.assign({
  rssFeed: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, selectors.STORE_NAME)]: (state, action) => action.resourceData.rssFeed || state,
    [FORM_SUBMIT_SUCCESS+'/'+editorSelectors.FORM_NAME]: (state, action) => action.updatedData
  })
}, editorReducer))

export {
  reducer
}
