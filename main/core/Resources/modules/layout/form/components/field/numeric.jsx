import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

const NumericInput = props =>
  <input
    id={props.id}
    type="number"
    className={classes('form-control', props.className)}
    value={null === props.value || isNaN(props.value) ? '' : props.value}
    disabled={props.disabled}
    min={props.min}
    max={props.max}
    placeholder={props.placeholder}
    onChange={(e) => props.onChange(Number(e.target.value))}
  />

NumericInput.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  disabled: T.bool,
  placeholder: T.string,
  value: T.oneOfType([T.number, T.string]),
  min: T.number,
  max: T.number,
  onChange: T.func.isRequired
}

// it's called Numeric to not override the default JS math object `Number`
const Numeric = props => props.unit ?
  <div className="input-group">
    <NumericInput {...props} />
    <span className="input-group-addon">
      {props.unit}
    </span>
  </div>
  :
  <NumericInput {...props} />

implementPropTypes(Numeric, FormFieldTypes, {
  value: T.oneOfType([T.number, T.string]),
  min: T.number,
  max: T.number,
  unit: T.string
}, {
  value: ''
})

export {
  Numeric
}
