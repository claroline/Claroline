import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {toKey} from '#/main/core/scaffolding/text'

const DataError = (props) => Array.isArray(props.error) ?
  <ul className="help-block-list">
    {props.error.map(error =>
      <li key={toKey(error)} className={classes('help-block error-block', {
        'error-block-warning': props.warnOnly,
        'error-block-danger': !props.warnOnly
      })}>
        <span className={classes('fa fa-fw icon-with-text-right', props.warnOnly ? 'fa-clock' : 'fa-warning')} />
        {error}
      </li>
    )}
  </ul> :
  <div className={classes('help-block error-block', {
    'error-block-warning': props.warnOnly,
    'error-block-danger': !props.warnOnly
  })}>
    <span className={classes('fa fa-fw icon-with-text-right', props.warnOnly ? 'fa-clock' : 'fa-warning')} />
    {props.error}
  </div>

DataError.propTypes = {
  error: T.oneOfType([
    T.string,           // a single error message
    T.arrayOf(T.string) // a list of error messages
  ]).isRequired,
  warnOnly: T.bool
}

DataError.defaultProps = {
  warnOnly: false
}

export {
  DataError
}
