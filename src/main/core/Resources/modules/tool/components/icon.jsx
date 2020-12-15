import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const ToolIcon = props =>
  <span className={classes('tool-icon fa', `fa-${props.type}`, props.className)} />

ToolIcon.propTypes = {
  className: T.string,
  type: T.string.isRequired
}

export {
  ToolIcon
}
