import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {icon} from '#/main/theme/config'

const ThemeIcon = props =>
  <img className={classes('theme-icon', props.className)} src={icon(props.mimeType)} />

ThemeIcon.propTypes = {
  className: T.string,
  mimeType: T.string.isRequired
}

export {
  ThemeIcon
}
