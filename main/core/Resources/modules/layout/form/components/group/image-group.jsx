import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group.jsx'
import {Image} from '#/main/core/layout/form/components/field/image.jsx'

const ImageGroup = props =>
  <FormGroup {...props}>
    <Image {...props} />
  </FormGroup>

implementPropTypes(ImageGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.object
}, {

})

export {
  ImageGroup
}
