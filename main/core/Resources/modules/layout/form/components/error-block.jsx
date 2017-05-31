import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

export const ErrorBlock = ({text, inGroup, warnOnly}) =>
  <span className={classes('help-block', {
    'text-warning': !inGroup && warnOnly,
    'text-danger': !inGroup && !warnOnly
  })}>
    <span className={classes('fa', warnOnly ? 'fa-clock-o' : 'fa-warning')}/>
    {text}
  </span>

ErrorBlock.propTypes = {
  text: T.string.isRequired,
  inGroup: T.bool.isRequired,
  warnOnly: T.bool.isRequired
}

ErrorBlock.defaultProps = {
  inGroup: false,
  warnOnly: false
}
