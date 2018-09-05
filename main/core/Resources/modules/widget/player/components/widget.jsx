import React from 'react'
import {PropTypes as T} from 'prop-types'
import sum from 'lodash/sum'
import times from 'lodash/times'

import {WidgetContent} from '#/main/core/widget/content/components/content'
import {
  WidgetContainer as WidgetContainerTypes,
  WidgetInstance as WidgetInstanceTypes
} from '#/main/core/widget/prop-types'
import {
  computeStyles,
  computeTitleStyles
} from '#/main/core/widget/utils'

const WidgetCol = props =>
  <div className={`widget-col col-md-${props.size}`}>
    {props.content &&
      <WidgetContent
        instance={props.content}
        context={props.context}
      />
    }
  </div>

WidgetCol.propTypes = {
  size: T.number.isRequired,
  context: T.object,
  content: T.shape(
    WidgetInstanceTypes.propTypes
  )
}

/**
 * Loads a widget application and renders it.
 *
 * @param props
 * @constructor
 */
const Widget = props =>
  <section className="widget" style={computeStyles(props.widget)}>
    {props.widget.name &&
      <h2 className="h-first widget-title" style={computeTitleStyles(props.widget)}>{props.widget.name}</h2>
    }
    <div className="row">
      {times(props.widget.display.layout.length, col =>
        <WidgetCol
          key={col}
          size={(12 / sum(props.widget.display.layout)) * props.widget.display.layout[col]}
          context={props.context}
          content={props.widget.contents[col]}
        />
      )}
    </div>
  </section>

Widget.propTypes = {
  widget: T.shape(
    WidgetContainerTypes.propTypes
  ).isRequired,
  context: T.object
}

export {
  Widget
}
