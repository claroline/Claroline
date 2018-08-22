import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/url/resources/url/editor/store/selectors'

const reducer = {
  urlForm: makeFormReducer(selectors.FORM_NAME, {}, {
    data: makeReducer({}, {
      [RESOURCE_LOAD]: (state, action) => action.resourceData.url
    }),
    initialData: makeReducer({}, {
      [RESOURCE_LOAD]: (state, action) => action.resourceData.url
    })
  })
}

export {
  reducer
}
