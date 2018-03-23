import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group'

import {Locale} from '#/main/core/layout/form/components/field/locale'

const LocaleGroup = props =>
  <FormGroup {...props}>
    <Locale {...props} />
  </FormGroup>

implementPropTypes(LocaleGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.string
})

export {
  LocaleGroup
}
