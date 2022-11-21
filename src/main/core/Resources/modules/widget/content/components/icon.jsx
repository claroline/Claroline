import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {ThemeIcon} from '#/main/theme/components/icon'

const WidgetSourceIcon = props =>
  <ThemeIcon
    className={classes('widget-icon', props.className)}
    mimeType={`custom/${props.type}`}
    set="data"
  />

WidgetSourceIcon.propTypes = {
  className: T.string,
  type: T.string.isRequired
}

const WidgetContentIcon = props =>
  <ThemeIcon
    className={classes('widget-icon', props.className)}
    mimeType={`custom/${props.type}`}
    set="widgets"
  />

WidgetContentIcon.propTypes = {
  className: T.string,
  type: T.string.isRequired
}

export {
  WidgetSourceIcon,
  WidgetContentIcon
}
