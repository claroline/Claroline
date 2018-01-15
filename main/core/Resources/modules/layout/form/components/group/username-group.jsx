import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroup as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

// todo check uniqueness (maybe do it in the data type)

const UsernameGroup = props =>
  <FormGroup {...props}>
    <div className="input-group">
      <span className="input-group-addon">
        <span className="fa fa-fw fa-user" role="presentation" />
      </span>
      <input
        id={props.id}
        type="text"
        className="form-control"
        value={props.value || ''}
        disabled={props.disabled}
        onChange={(e) => props.onChange(e.target.value)}
      />
    </div>
  </FormGroup>

implementPropTypes(UsernameGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.string
}, {
  value: ''
})

export {
  UsernameGroup
}