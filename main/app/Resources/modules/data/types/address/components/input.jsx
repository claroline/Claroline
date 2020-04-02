import React from 'react'
import classes from 'classnames'
import omit from 'lodash/omit'
import cloneDeep from 'lodash/cloneDeep'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {FormFieldset} from '#/main/app/content/form/components/fieldset'

import {Address as AddressTypes} from '#/main/app/data/types/address/prop-types'

const AddressInput = props =>
  <FormFieldset
    {...omit(props, 'value', 'onChange', 'onError')}
    className={classes('address-control', props.className)}
    data={props.value}
    updateProp={(name, value) => {
      const newAddress = props.value ? cloneDeep(props.value) : {}
      newAddress[name] = value

      props.onChange(newAddress)
    }}
    setErrors={(name, error) => {
      const newErrors = props.errors ? cloneDeep(props.errors) : {}
      newErrors[name] = error

      props.onError(newErrors)
    }}
    fields={[
      {
        name: 'street1',
        label: trans('address_street'),
        type: 'string'
      }, {
        name: 'street2',
        label: trans('address_street'),
        hideLabel: true,
        type: 'string'
      }, {
        name: 'postalCode',
        label: trans('address_postal_code'),
        type: 'string'
      }, {
        name: 'city',
        label: trans('address_city'),
        type: 'string'
      }, {
        name: 'state',
        label: trans('address_state'),
        type: 'string'
      }, {
        name: 'country',
        label: trans('address_country'),
        type: 'country'
      }
    ]}
  />

implementPropTypes(AddressInput, DataInputTypes, {
  // more precise value type
  value: T.shape(
    AddressTypes.propTypes
  )
}, {
  value: {}
})

export {
  AddressInput
}
