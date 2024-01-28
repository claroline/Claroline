import React from 'react'
import {PropTypes as T} from 'prop-types'
import sum from 'lodash/sum'
import times from 'lodash/times'
import omit from 'lodash/omit'

import {WidgetContent} from '#/main/core/widget/content/containers/content'
import {WidgetContainer as WidgetContainerTypes, WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/prop-types'
import {WidgetContainer} from '#/main/core/widget/components/container'

const WidgetCol = props =>
  <div className={`widget-col col-md-${props.size}`}>
    {props.content &&
      <WidgetContent
        instance={props.content}
        currentContext={props.currentContext}
        display={props.display}
      />
    }
  </div>

WidgetCol.propTypes = {
  size: T.number.isRequired,
  currentContext: T.object,
  content: T.shape(
    WidgetInstanceTypes.propTypes
  ),
  display: T.object
}

/**
 * Loads a widget application and renders it.
 *
 * @param props
 * @constructor
 */
const Widget = props =>
  <WidgetContainer widget={props.widget}>
    <div className="row">
      {times(props.widget.display.layout.length, col =>
        <WidgetCol
          key={col}
          size={(12 / sum(props.widget.display.layout)) * props.widget.display.layout[col]}
          currentContext={props.currentContext}
          content={props.widget.contents[col]}
          display={omit(props.widget.display, 'layout')}
        />
      )}
    </div>
  </WidgetContainer>

Widget.propTypes = {
  widget: T.shape(
    WidgetContainerTypes.propTypes
  ).isRequired,
  currentContext: T.object
}

export {
  Widget
}
