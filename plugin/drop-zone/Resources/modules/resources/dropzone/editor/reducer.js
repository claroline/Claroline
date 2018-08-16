import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {select} from '#/plugin/drop-zone/resources/dropzone/store/selectors'

const reducer = makeFormReducer(`${select.STORE_NAME}.dropzoneForm`, {}, {
  data: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.dropzone
  }),
  originalData: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.dropzone
  })
})

export {
  reducer
}
