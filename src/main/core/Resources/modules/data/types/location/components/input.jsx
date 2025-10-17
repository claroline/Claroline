import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {EntityInput} from '#/main/app/data/types/entity'

import {Location as LocationTypes} from '#/main/core/data/types/location/prop-types'
import {MODAL_LOCATIONS} from '#/main/core/modals/locations'

const LocationInput = (props) =>
  <EntityInput
    {...props}
    add={trans(props.multiple ? 'add_locations' : 'add_location', {}, 'actions')}
    pickerType={MODAL_LOCATIONS}
  />

implementPropTypes(LocationInput, DataInputTypes, {
  value: T.oneOfType([
    T.shape(LocationTypes.propTypes),
    T.arrayOf(
      T.shape(LocationTypes.propTypes)
    )
  ]),
})

export {
  LocationInput
}
