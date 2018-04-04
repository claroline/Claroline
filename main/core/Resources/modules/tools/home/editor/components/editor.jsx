import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {TooltipAction} from '#/main/core/layout/button/components/tooltip-action'

import {Widget} from '#/main/core/widget/components/widget'
import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/prop-types'

import {select} from '#/main/core/tools/home/selectors'
import {select as editorSelect} from '#/main/core/tools/home/editor/selectors'
import {actions} from '#/main/core/tools/home/editor/actions'

const WidgetEditor = props =>
  <div className="widget-container">
    <TooltipAction
      id={`add-before-${props.instance.id}`}
      className="btn-link-default"
      icon="fa fa-fw fa-plus"
      label={trans('add_widget_before', {}, 'widget')}
      action={props.insert}
    />

    <TooltipAction
      id={`edit-${props.id}`}
      className="btn-link-default"
      icon="fa fa-fw fa-pencil"
      label={trans('edit')}
      action={() => props.edit(props.instance.id)}
    />

    <TooltipAction
      id={`delete-${props.instance.id}`}
      className="btn-link-danger"
      icon="fa fa-fw fa-trash-o"
      label={trans('delete')}
      action={() => props.delete(props.instance.id)}
    />

    <Widget
      instance={props.instance}
      context={props.context}
    />
  </div>

WidgetEditor.propTypes = {
  context: T.object.isRequired,
  instance: T.shape(
    WidgetInstanceTypes.propTypes
  ).isRequired,
  insert: T.func.isRequired,
  edit: T.func.isRequired,
  delete: T.func.isRequired
}

const EditorComponent = props =>
  <div>
    {props.widgets.map((widgetInstance, index) =>
      <WidgetEditor
        key={index}
        instance={widgetInstance}
        context={props.context}
        insert={() => props.insertWidget(props.context, index)}
        edit={() => props.editWidget(index, widgetInstance)}
        delete={() => props.deleteWidget(index)}
      />
    )}

    <button
      className="btn btn-block btn-primary btn-add"
      onClick={() => props.insertWidget(props.context)}
    >
      {trans('add_widget', {}, 'widget')}
    </button>
  </div>

EditorComponent.propTypes = {
  context: T.object.isRequired,
  widgets: T.arrayOf(T.shape(
    WidgetInstanceTypes.propTypes
  )).isRequired,
  insertWidget: T.func.isRequired,
  editWidget: T.func.isRequired,
  deleteWidget: T.func.isRequired
}

const Editor = connect(
  state => ({
    context: select.context(state),
    widgets: editorSelect.widgets(state),
    tabs: editorSelect.widgets(state)
  }),
  dispatch => ({
    insertWidget(context, position) {
      dispatch(actions.insertWidget(context, position))
    },
    editWidget(position, widget) {
      dispatch(actions.editWidget(position, widget))
    },
    deleteWidget(position) {
      dispatch(actions.deleteWidget(position))
    }
  })
)(EditorComponent)

export {
  Editor
}
