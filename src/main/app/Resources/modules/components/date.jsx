import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {displayDate} from '#/main/app/intl'

const Datetime = (props) =>
  <time
    className={classes('text-nowrap', props.className)}
    dateTime={props.value}
  >
    {displayDate(props.value, props.long, props.time)}
  </time>

Datetime.propTypes = {
  className: T.string,
  value: T.string.isRequired,
  long: T.bool,
  time: T.bool
}

export {
  Datetime
}
