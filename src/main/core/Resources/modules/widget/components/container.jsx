import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {ContentTitle} from '#/main/app/content/components/title'

import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'
import {computeStyles, computeTitleStyles, computeBodyStyles} from '#/main/core/widget/utils'
import {Heading} from '#/main/app/components/heading'

const WidgetContainer = props =>
  <section
    {...omit(props, 'widget', 'children')}
    className={classes('widget-container row position-relative', props.className)}
    style={computeStyles(props.widget)}
    id={`section-${props.widget.id}`}
  >
    {(props.widget.name || props.widget.description) &&
      <Heading
        level={2}
        displayLevel={get(props.widget, 'display.titleLevel')}
        className="widget-title"
        align={get(props.widget, 'display.alignName')}
        style={computeTitleStyles(props.widget)}
        title={props.widget.name}
        subtitle={props.widget.description}
      />
    }

    <div className="widget-body" style={computeBodyStyles(props.widget)} role="presentation">
      {props.children}
    </div>
  </section>

WidgetContainer.propTypes = {
  className: T.string,
  widget: T.shape(
    WidgetContainerTypes.propTypes
  ).isRequired,
  children: T.any
}

export {
  WidgetContainer
}
