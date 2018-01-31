import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

export const ErrorBlock = ({text, inGroup, warnOnly}) =>
  <span className={classes('help-block error-block', {
    'error-block-warning': !inGroup && warnOnly,
    'error-block-danger': !inGroup && !warnOnly
  })}>
    <span className={classes('fa fa-fw', warnOnly ? 'fa-clock-o' : 'fa-warning')}/>
    {text}
  </span>

ErrorBlock.propTypes = {
  text: T.string.isRequired,
  inGroup: T.bool,
  warnOnly: T.bool
}

ErrorBlock.defaultProps = {
  inGroup: false,
  warnOnly: false
}
