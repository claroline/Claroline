import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Widget} from '#/main/core/widget/player/components/widget'
import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'

const WidgetGrid = props => {
  let visibleWidgets = props.widgets.filter(widget => widget.visible === true)
  return(
    <div className="widgets-grid">
      {visibleWidgets.map((widget, index) =>
        <Widget
          key={index}
          widget={widget}
          context={props.context}
        />
      )}
    </div>
  )
}


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
