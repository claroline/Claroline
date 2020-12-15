import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store'

import {selectors as lessonSelectors} from '#/plugin/lesson/resources/lesson/store/selectors'
import {selectors} from '#/plugin/lesson/resources/lesson/editor/store/selectors'

export const reducer = makeFormReducer(selectors.STORE_NAME, {}, {
  data: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, lessonSelectors.STORE_NAME)]: (state, action) => action.resourceData.lesson
  }),
  originalData: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, lessonSelectors.STORE_NAME)]: (state, action) => action.resourceData.lesson
  })
})
