import React from 'react'
import {PropTypes as T} from 'prop-types'

import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {CascadeSelect} from '#/main/core/layout/form/components/field/cascade-select.jsx'

const CascadeSelectGroup = props =>
  <FormGroup
    {...props}
  >
    <CascadeSelect
      options={props.options}
      selectedValue={props.selectedValue}
      disabled={props.disabled}
      onChange={props.onChange}
    />
  </FormGroup>

CascadeSelectGroup.propTypes = {
  controlId: T.string.isRequired,
  options: T.array.isRequired,
  selectedValue: T.array.isRequired,
  disabled: T.bool,
  onChange: T.func.isRequired
}

export {
  CascadeSelectGroup
}
