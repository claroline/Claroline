import React from 'react'
import {connect} from 'react-redux'

import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {selectors} from '#/main/core/resources/text/editor/store'

import {Editor as EditorComponent} from '#/main/core/resources/text/editor/components/editor'

const Editor = connect(
  state => ({
    path: resourceSelectors.path(state),
    workspace: resourceSelectors.workspace(state),
    text: selectors.text(state),
    availablePlaceholders: selectors.availablePlaceholders(state),
    value: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)).raw
  }),
  (dispatch) => ({
    updateProp(prop, value) {
      dispatch(formActions.updateProp(selectors.FORM_NAME, prop, value))
    }
  })
)(EditorComponent)

export {
  Editor
}
