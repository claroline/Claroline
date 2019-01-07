import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {LocationCard} from '#/main/core/user/data/components/location-card'
import {Location as LocationType} from '#/main/core/user/prop-types'
import {MODAL_LOCATIONS_PICKER} from '#/main/core/modals/locations'

const LocationsButton = props =>
  <Button
    className="btn"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-location-arrow"
    label={trans('select_a_location')}
    primary={true}
    modal={[MODAL_LOCATIONS_PICKER, {
      url: ['apiv2_location_list'],
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected[0])
      })
    }]}
  />

LocationsButton.propTypes = {
  title: T.string,
  onChange: T.func.isRequired
}

const LocationInput = props => {
  if (props.value) {
    return(
      <div>
        <LocationCard
          data={props.value}
          actions={[
            {
              name: 'delete',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash-o',
              label: trans('delete', {}, 'actions'),
              dangerous: true,
              callback: () => props.onChange(null)
            }
          ]}
        />

        <LocationsButton
          {...props.picker}
          onChange={props.onChange}
        />
      </div>
    )
  } else {
    return (
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-location-arrow"
        title={trans('no_location')}
      >
        <LocationsButton
          {...props.picker}
          onChange={props.onChange}
        />
      </EmptyPlaceholder>
    )
  }
}

implementPropTypes(LocationInput, FormFieldTypes, {
  value: T.shape(LocationType.propTypes),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null,
  picker: {
    title: trans('location_selector')
  }
})

export {
  LocationInput
}
