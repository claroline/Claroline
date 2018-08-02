import {makeReducer} from '#/main/app/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {reducer as editorReducer} from '#/main/core/resources/text/editor/reducer'

const reducer = {
  textForm: editorReducer.textForm,
  text: makeReducer({}, {
    // replaces path data after success updates
    [FORM_SUBMIT_SUCCESS+'/textForm']: (state, action) => action.updatedData
  })
}

export {
  reducer
}