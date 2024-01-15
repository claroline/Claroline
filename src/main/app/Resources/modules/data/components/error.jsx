import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {toKey} from '#/main/core/scaffolding/text'

const DataError = (props) => {
  if (Array.isArray(props.error)) {
    return (
      <>
        {props.error.map(error =>
          <div key={toKey(error)} className={classes({
            'incomplete-feedback': props.warnOnly,
            'invalid-feedback': !props.warnOnly
          })}>
            {error}
          </div>
        )}
      </>
    )
  }

  return (
    <div className={classes({
      'incomplete-feedback': props.warnOnly,
      'invalid-feedback': !props.warnOnly
    })}>
      {props.error}
    </div>
  )
}

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
