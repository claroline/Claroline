import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/main/core/resources/file/editor/store/selectors'

const reducer = {
  fileForm: makeFormReducer(selectors.FORM_NAME, {}, {
    data: makeReducer({}, {
      [RESOURCE_LOAD]: (state, action) => action.resourceData.file
    }),
    initialData: makeReducer({}, {
      [RESOURCE_LOAD]: (state, action) => action.resourceData.file
    })
  })
}

export {
  reducer
}
