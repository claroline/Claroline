import React, {PropTypes as T} from 'react'
import classes from 'classnames'

export const ErrorBlock = ({text, inGroup, warnOnly}) =>
  <span className={classes('help-block', {
    'warning-text': !inGroup && warnOnly,
    'error-text': !inGroup && !warnOnly
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
