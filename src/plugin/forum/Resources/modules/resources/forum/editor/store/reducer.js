import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store'

import {selectors as forumSelectors} from '#/plugin/forum/resources/forum/store/selectors'
import {selectors} from '#/plugin/forum/resources/forum/editor/store/selectors'

const reducer = makeFormReducer(selectors.FORM_NAME, {}, {
  data: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, forumSelectors.STORE_NAME)]: (state, action) => action.resourceData.forum
  }),
  originalData: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, forumSelectors.STORE_NAME)]: (state, action) => action.resourceData.forum
  })
})

export {
  reducer
}
