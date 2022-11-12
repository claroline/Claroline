import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {HtmlInput} from '#/main/app/data/types/html/components/input'
import {DataGroup as DataGroupTypes, DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'

const HtmlGroup = props =>
  <FormGroup {...props}>
    <HtmlInput {...props} />
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
