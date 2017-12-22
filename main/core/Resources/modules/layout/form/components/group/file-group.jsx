import React from 'react'
import {PropTypes as T} from 'prop-types'

import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {File} from '#/main/core/layout/form/components/field/file.jsx'

const FileGroup = props =>
  <FormGroup {...props}>
    <File
      controlId={props.controlId}
      value={props.value || []}
      disabled={props.disabled}
      types={props.types}
      max={props.max}
      onChange={(value) => props.onChange(value)}
    />
  </FormGroup>

FileGroup.propTypes = {
  controlId: T.string.isRequired,
  value: T.array,
  types: T.arrayOf(T.string).isRequired,
  max: T.number.isRequired,
  disabled: T.bool.isRequired,
  onChange: T.func.isRequired
}

FileGroup.defaultProps = {
  value: '',
  disabled: false,
  types: [],
  max: 1,
  onChange: () => {}
}

export {
  FileGroup
}
