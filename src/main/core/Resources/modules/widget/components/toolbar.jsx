import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import {Toolbar} from '#/main/app/action'

const WidgetToolbar = (props) => !isEmpty(props.actions) &&
  <Toolbar
    id={`${props.widget.id}-actions`}
    className="btn-group widget-toolbar position-absolute end-0 top-0"
    buttonName="btn btn-primary"
    disabled={props.disabled}
    tooltip="bottom"
    actions={props.actions}
    size="sm"
  />

WidgetToolbar.propTypes = {
  disabled: T.bool,
  widget: T.shape({

  }),
  actions: T.arrayOf(T.shape({

  })),
  updateProp: T.func.isRequired
}

export {
  WidgetToolbar
}
