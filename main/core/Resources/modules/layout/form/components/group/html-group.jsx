import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group'

import {Textarea} from '#/main/core/layout/form/components/field/textarea'

const HtmlGroup = props =>
  <FormGroup {...props}>
    <Textarea {...props} />
  </FormGroup>

implementPropTypes(HtmlGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.string,
  // custom props
  minimal: T.bool,
  minRows: T.number,
  onSelect: T.func,
  onClick: T.func,
  onChangeMode: T.func
}, {
  value: '',
  minimal: true
})

export {
  HtmlGroup
}
