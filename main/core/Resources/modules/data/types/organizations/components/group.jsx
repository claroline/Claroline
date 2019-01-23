import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'
import {Organization as OrganizationType} from '#/main/core/user/prop-types'
import {OrganizationsInput} from '#/main/core/data/types/organizations/components/input'

const OrganizationsGroup = props =>
  <FormGroup {...props}>
    <OrganizationsInput {...props} />
  </FormGroup>

implementPropTypes(OrganizationsGroup, FormGroupWithFieldTypes, {
  value: T.arrayOf(T.shape(OrganizationType.propTypes))
})

export {
  OrganizationsGroup
}
