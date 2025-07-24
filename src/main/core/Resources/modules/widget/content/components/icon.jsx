import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {ThemeIcon} from '#/main/theme/components/icon'

const WidgetSourceIcon = props =>
  <ThemeIcon
    className={classes('widget-icon flex-shrink-0', props.className)}
    mimeType={`custom/${props.type}`}
    set="data"
    size={props.size}
  />

WidgetSourceIcon.propTypes = {
  className: T.string,
  type: T.string.isRequired,
  size: T.string
}

const WidgetContentIcon = props =>
  <ThemeIcon
    className={classes('widget-icon flex-shrink-0', props.className)}
    mimeType={`custom/${props.type}`}
    set="widgets"
    size={props.size}
  />

WidgetContentIcon.propTypes = {
  className: T.string,
  type: T.string.isRequired,
  size: T.string
}

export {
  WidgetSourceIcon,
  WidgetContentIcon
}
