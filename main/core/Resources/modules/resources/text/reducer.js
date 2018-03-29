import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makeResourceReducer} from '#/main/core/resource/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'
import {reducer as editorReducer} from '#/main/core/resources/text/editor/reducer'

const reducer = makeResourceReducer({}, {
  textForm: editorReducer.textForm,
  text: makeReducer({}, {
    // replaces path data after success updates
    [FORM_SUBMIT_SUCCESS+'/textForm']: (state, action) => action.updatedData
  })
})

export {
  reducer
}