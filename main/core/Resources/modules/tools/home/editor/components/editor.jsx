import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {actions as formActions} from '#/main/core/data/form/actions'

import {WidgetGridEditor} from '#/main/core/widget/editor/components/grid'
import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'

import {select} from '#/main/core/tools/home/selectors'
import {select as editorSelect} from '#/main/core/tools/home/editor/selectors'

const EditorComponent = props =>
  <WidgetGridEditor
    context={props.context}
    widgets={props.widgets}
    update={props.update}
  />

EditorComponent.propTypes = {
  context: T.object.isRequired,
  widgets: T.arrayOf(T.shape(
    WidgetContainerTypes.propTypes
  )).isRequired,
  update: T.func.isRequired
}

const Editor = connect(
  state => ({
    context: select.context(state),
    widgets: editorSelect.widgets(state),
    tabs: editorSelect.tabs(state)
  }),
  dispatch => ({
    update(widgets) {
      dispatch(formActions.updateProp('editor', 'widgets', widgets))
    }
  })
)(EditorComponent)

export {
  Editor
}
