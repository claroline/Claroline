import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {Textarea} from '#/main/core/layout/form/components/field/textarea.jsx'

const HtmlGroup = props =>
  <FormGroup
    {...props}
  >
    <Textarea
      id={props.id}
      value={props.value}
      minRows={props.minRows}
      disabled={props.disabled}
      onChange={props.onChange}
      onClick={props.onClick}
      onSelect={props.onSelect}
      onChangeMode={props.onChangeMode}
    />
  </FormGroup>

implementPropTypes(HtmlGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.string,
  // custom props
  minRows: T.number,
  onSelect: T.func,
  onClick: T.func,
  onChangeMode: T.func
})

export {
  HtmlGroup
}
