import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors as rssSelectors} from '#/plugin/rss/resources/rss-feed/store/selectors'
import {selectors} from '#/plugin/rss/resources/rss-feed/editor/store/selectors'

const reducer = {
  rssFeedForm: makeFormReducer(selectors.FORM_NAME, {}, {
    originalData: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, rssSelectors.STORE_NAME)]: (state, action) => action.resourceData.slideshow || state
    }),
    data: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, rssSelectors.STORE_NAME)]: (state, action) => action.resourceData.slideshow || state
    })
  })
}

export {
  reducer
}
