import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group'

import {TagInput} from '#/plugin/tag/data/tag/components/input'

const TagGroup = props =>
  <FormGroup {...props}>
    <TagInput {...props} />
  </FormGroup>

implementPropTypes(TagGroup, FormGroupWithFieldTypes, {

})

export {
  TagGroup
}
