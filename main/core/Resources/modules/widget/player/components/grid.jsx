import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Widget} from '#/main/core/widget/player/components/widget'
import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'

const WidgetGrid = props =>
  <div className="widgets-grid">
    {props.widgets.map((widget, index) =>
      <Widget
        key={index}
        widget={widget}
        context={props.context}
      />
    )}
  </div>

WidgetGrid.propTypes = {
  context: T.object,
  widgets: T.arrayOf(T.shape(
    WidgetContainerTypes.propTypes
  ))
}

WidgetGrid.defaultProps = {
  widgets: []
}

export {
  WidgetGrid
}
