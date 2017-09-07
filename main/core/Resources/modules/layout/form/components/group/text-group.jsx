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
        onChange={(e) => props.onChange(e.target.value)}
        rows={3}
      />
    }

    {!props.long &&
      <input
        id={props.controlId}
        type="text"
        className="form-control"
        value={props.value || ''}
        onChange={(e) => props.onChange(e.target.value)}
      />
    }
  </FormGroup>

TextGroup.propTypes = {
  controlId: T.string.isRequired,
  long: T.bool,
  value: T.string,
  onChange: T.func.isRequired
}

TextGroup.defaultProps = {
  value: '',
  long: false
}

export {
  TextGroup
}
