import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const ContentError = ({text, inGroup, warnOnly}) =>
  <span className={classes('help-block error-block', {
    'error-block-warning': !inGroup && warnOnly,
    'error-block-danger': !inGroup && !warnOnly
  })}>
    <span className={classes('fa fa-fw', warnOnly ? 'fa-clock-o' : 'fa-warning')} />
    {text}
  </span>

ContentError.propTypes = {
  text: T.string.isRequired,
  inGroup: T.bool,
  warnOnly: T.bool
}

ContentError.defaultProps = {
  inGroup: false,
  warnOnly: false
}

export {
  ContentError
}
