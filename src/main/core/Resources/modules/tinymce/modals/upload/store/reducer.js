import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeFormReducer} from '#/main/app/content/form/store'

import {selectors} from '#/main/core/tinymce/modals/upload/store/selectors'
import {UPLOAD_DESTINATIONS_LOAD} from '#/main/core/tinymce/modals/upload/store/actions'

const reducer = combineReducers({
  uploadDestinations: makeReducer([], {
    [UPLOAD_DESTINATIONS_LOAD]: (state, action) => action.directories
  }),
  form: makeFormReducer(selectors.FORM_NAME, {
    new: true
  })
})

export {
  reducer
}
