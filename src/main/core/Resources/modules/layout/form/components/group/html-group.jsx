import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {DataGroup as DataGroupTypes, DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'

import {Textarea} from '#/main/core/layout/form/components/field/textarea'

const HtmlGroup = props =>
  <FormGroup {...props}>
    <Textarea {...props} />
  </FormGroup>

implementPropTypes(HtmlGroup, [DataGroupTypes, DataInputTypes], {
  // more precise value type
  value: T.string,
  // custom props
  minimal: T.bool,
  minRows: T.number,
  onSelect: T.func,
  onClick: T.func,
  onChangeMode: T.func,
  workspace: T.object
}, {
  value: '',
  minimal: true
})

export {
  HtmlGroup
}
