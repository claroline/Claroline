import {connect} from 'react-redux'

import {selectors as formSelectors, actions as formActions} from '#/main/app/content/form/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {Editor as EditorComponent} from '#/plugin/url/resources/url/editor/components/editor'
import {selectors} from '#/plugin/url/resources/url/editor/store'

const Editor = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    url: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)),
    availablePlaceholders: selectors.availablePlaceholders(state)
  }),
  (dispatch) => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(selectors.FORM_NAME, propName, propValue))
    }
  })
)(EditorComponent)

export {
  Editor
}
