import React from 'react'
import {PropTypes as T} from 'prop-types'

import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

const TextGroup = props =>
  <FormGroup
    {...props}
  >
    {props.long &&
      <textarea
        id={props.controlId}
        className="form-control"
        value={props.value || ''}
        disabled={props.disabled}
        onChange={(e) => props.onChange(e.target.value)}
        rows={props.minRows}
      />
    }

    {!props.long &&
      <input
        id={props.controlId}
        type="text"
        className="form-control"
        value={props.value || ''}
        disabled={props.disabled}
        onChange={(e) => props.onChange(e.target.value)}
      />
    }
  </FormGroup>

TextGroup.propTypes = {
  controlId: T.string.isRequired,
  long: T.bool,
  minRows: T.number,
  value: T.string,
  disabled: T.bool.isRequired,
  onChange: T.func.isRequired
}

TextGroup.defaultProps = {
  value: '',
  long: false,
  minRows: 2,
  disabled: false
}

export {
  TextGroup
}
