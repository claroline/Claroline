import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group.jsx'
import {ColorPicker} from '#/main/core/layout/form/components/field/color-picker.jsx'

const ColorGroup = props =>
  <FormGroup {...props}>
    <ColorPicker {...props} />
  </FormGroup>

implementPropTypes(ColorGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.string,
  colors: T.arrayOf(T.string),
  forFontColor: T.bool,
  autoOpen: T.bool
})

export {
  ColorGroup
}
