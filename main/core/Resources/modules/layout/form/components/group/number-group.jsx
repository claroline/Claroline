import React from 'react'
import {PropTypes as T} from 'prop-types'

import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

const NumberGroup = props =>
  <FormGroup {...props}>
    <input
      id={props.controlId}
      type="number"
      className="form-control"
      value={isNaN(props.value) ? undefined : props.value}
      disabled={props.disabled}
      min={props.min}
      max={props.max}
      onChange={(e) => props.onChange(e.target.value)}
    />
  </FormGroup>

NumberGroup.propTypes = {
  controlId: T.string.isRequired,
  value: T.oneOfType([T.number, T.string]),
  min: T.number,
  max: T.number,
  disabled: T.bool,
  onChange: T.func.isRequired
}

export {
  NumberGroup
}
