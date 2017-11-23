import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const Numeric = props =>
  <input
    id={props.id}
    type="number"
    className={classes('form-control', props.className)}
    value={isNaN(props.value) ? '' : props.value}
    disabled={props.disabled}
    min={props.min}
    max={props.max}
    onChange={(e) => props.onChange(Number(e.target.value))}
  />

Numeric.propTypes = {
  id: T.string.isRequired,
  value: T.oneOfType([T.number, T.string]),
  min: T.number,
  max: T.number,
  disabled: T.bool,
  className: T.string,
  onChange: T.func.isRequired
}

Numeric.defaultProps = {
  disabled: false
}

export {
  Numeric
}
