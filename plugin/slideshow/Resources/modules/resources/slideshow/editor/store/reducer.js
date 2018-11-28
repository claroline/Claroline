import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/slideshow/resources/slideshow/editor/store/selectors'

const reducer = {
  slideshowForm: makeFormReducer(selectors.FORM_NAME, {}, {
    originalData: makeReducer({}, {
      [RESOURCE_LOAD]: (state, action) => action.resourceData.slideshow || state
    }),
    data: makeReducer({}, {
      [RESOURCE_LOAD]: (state, action) => action.resourceData.slideshow || state
    })
  })
}

export {
  reducer
}
