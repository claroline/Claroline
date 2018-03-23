import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {Url} from '#/main/core/layout/form/components/field/url.jsx'

const UrlGroup = props =>
  <FormGroup {...props}>
    <Url {...props} />
  </FormGroup>

implementPropTypes(UrlGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.string
})

export {
  UrlGroup
}
