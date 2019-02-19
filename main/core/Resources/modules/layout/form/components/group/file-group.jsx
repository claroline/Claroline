import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group.jsx'

import {File} from '#/main/core/layout/form/components/field/file.jsx'

const FileGroup = props =>
  <FormGroup {...props}>
    <File {...props} />
  </FormGroup>

implementPropTypes(FileGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.oneOfType([T.array, T.object]),
  // custom props
  types: T.arrayOf(T.string),
  multiple: T.bool,
  min: T.number,
  max: T.number,
  autoUpload: T.bool,
  uploadUrl: T.array
})

export {
  FileGroup
}
