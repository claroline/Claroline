import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroup as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

const TextGroup = props =>
  <FormGroup
    {...props}
  >
    {props.long &&
      <textarea
        id={props.id}
        className="form-control"
        value={props.value || ''}
        disabled={props.disabled}
        onChange={(e) => props.onChange(e.target.value)}
        rows={props.minRows}
      />
    }

    {!props.long &&
      <input
        id={props.id}
        type="text"
        className="form-control"
        value={props.value || ''}
        disabled={props.disabled}
        onChange={(e) => props.onChange(e.target.value)}
      />
    }
  </FormGroup>

implementPropTypes(TextGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.string,
  // custom props
  long: T.bool,
  minRows: T.number,
  minLength: T.number, // todo implement
  maxLength: T.number // todo implement
}, {
  value: '',
  long: false,
  minRows: 2
})

export {
  TextGroup
}
