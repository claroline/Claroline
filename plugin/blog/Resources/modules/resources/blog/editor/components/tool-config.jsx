import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {Button} from '#/main/app/action/components/button'
import ButtonGroup from 'react-bootstrap/lib/ButtonGroup'
import {actions as optionsActions} from '#/plugin/blog/resources/blog/editor/store'

const ToolConfigComponent = props =>
  <li className="clearfix">
    <ButtonGroup className="order-widgets">
      <Button
        type="callback"
        label=""
        className="btn btn-link widget-ordering-button"
        icon="fa fa-fw fa-2x fa-caret-up"
        callback={() => {
          props.widgetUp(props.id, props.templateName)
        }}
        disabled={props.index <= 0}
      />
      <Button
        type="callback"
        className="btn btn-link widget-ordering-button"
        label=""
        icon="fa fa-fw fa-2x fa-caret-down"
        callback={() => {
          props.widgetDown(props.id, props.templateName)
        }}
        disabled={props.index >= props.max}
      />
      <Button
        type="callback"
        className="btn btn-link widget-ordering-button"
        label=""
        icon={props.visibility ? 'fa fa-fw fa-eye fa-lg' : 'fa fa-fw fa-eye-slash fa-lg'}
        callback={() => {
          props.switchVisiblity(props.id, props.templateName)
        }}
      />
    </ButtonGroup>
    <span className="widget-label">{props.label}</span>
  </li>

ToolConfigComponent.propTypes = {
  id: T.number.isRequired,
  index: T.number.isRequired,
  max: T.number.isRequired,
  visibility: T.bool,
  label: T.string.isRequired,
  templateName: T.string.isRequired,
  switchVisiblity: T.func.isRequired,
  widgetUp: T.func.isRequired,
  widgetDown: T.func.isRequired
}

const ToolConfig = connect(
  null,
  dispatch => ({
    switchVisiblity: (id, name) => {
      dispatch(
        optionsActions.switchWidgetVisibility(id, name)
      )
    },
    widgetUp: (id, name) => {
      dispatch(
        optionsActions.widgetUp(id, name)
      )
    },
    widgetDown: (id, name) => {
      dispatch(
        optionsActions.widgetDown(id, name)
      )
    }
  })
)(ToolConfigComponent)
  
export {ToolConfig}