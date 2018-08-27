import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'

import {WidgetContentIcon, WidgetSourceIcon} from '#/main/core/widget/content/components/icon'

const WidgetSourceType = props =>
  <article className="widget-type">
    <WidgetSourceIcon type={props.source} />

    <div>
      <h1>{trans(props.source, {}, 'data_sources')}</h1>
      <p className="hidden-xs">{trans(`${props.source}_desc`, {}, 'data_sources')}</p>
    </div>
  </article>

WidgetSourceType.propTypes = {
  source: T.string.isRequired
}

/**
 * Renders the name and icon of a Widget.
 */
const WidgetContentType = props =>
  <article className="widget-type">
    <WidgetContentIcon type={props.type} />

    <div>
      <h1>{trans(props.type, {}, 'widget')}</h1>
      <p className="hidden-xs">{trans(`${props.type}_desc`, {}, 'widget')}</p>
    </div>
  </article>

WidgetContentType.propTypes = {
  type: T.string.isRequired
}

export {
  WidgetSourceType,
  WidgetContentType
}