import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'
import {Location as LocationType} from '#/main/core/user/prop-types'
import {LocationInput} from '#/main/core/data/types/location/components/input'

const LocationGroup = props => {
  return(<FormGroup {...props}>
    <LocationInput {...props} />
  </FormGroup>)
}

implementPropTypes(LocationGroup, FormGroupWithFieldTypes, {
  value: T.shape(LocationType.propTypes)
})

export {
  LocationGroup
}
