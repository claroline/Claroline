import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors as webResourceSelectors} from '#/plugin/web-resource/resources/web-resource/store/selectors'
import {selectors} from '#/plugin/web-resource/resources/web-resource/editor/store/selectors'

const reducer = makeFormReducer(selectors.FORM_NAME, {}, {
  data: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, webResourceSelectors.STORE_NAME)]: (state, action) => action.resourceData.file
  }),
  initialData: makeReducer({}, {
    [makeInstanceAction(RESOURCE_LOAD, webResourceSelectors.STORE_NAME)]: (state, action) => action.resourceData.file
  })
})


export {
  reducer
}
