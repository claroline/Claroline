import {connect} from 'react-redux'

import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {Editor as EditorComponent} from '#/plugin/lesson/resources/lesson/editor/components/editor'
import {selectors} from '#/plugin/lesson/resources/lesson/editor/store'
import {actions} from '#/plugin/lesson/resources/lesson/store'

const Editor = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    lesson: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)),
    workspace: resourceSelectors.workspace(state)
  }),
  (dispatch) => ({
    saveForm(id) {
      dispatch(formActions.saveForm(selectors.STORE_NAME, ['icap_lesson_update', {id: id}])).then(() => {
        dispatch(actions.fetchChapterTree(id))
      })
    }
  })
)(EditorComponent)

export {
  Editor
}
