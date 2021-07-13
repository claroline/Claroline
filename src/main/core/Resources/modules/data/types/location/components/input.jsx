import React, {Fragment} from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {LocationCard} from '#/main/core/data/types/location/components/card'
import {Location as LocationTypes} from '#/main/core/user/prop-types'
import {MODAL_LOCATIONS} from '#/main/core/modals/locations'

const LocationsButton = props =>
  <Button
    className="btn btn-block"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={trans('add_location')}
    disabled={props.disabled}
    modal={[MODAL_LOCATIONS, {
      url: ['apiv2_location_list'],
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected[0])
      })
    }]}
    size={props.size}
  />

LocationsButton.propTypes = {
  title: T.string,
  disabled: T.bool,
  onChange: T.func.isRequired,
  size: T.string
}

const LocationInput = props => {
  if (props.value) {
    return (
      <Fragment>
        <LocationCard
          data={props.value}
          size="xs"
          actions={[
            {
              name: 'delete',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash-o',
              label: trans('delete', {}, 'actions'),
              dangerous: true,
              disabled: props.disabled,
              callback: () => props.onChange(null)
            }
          ]}
        />

        <LocationsButton
          {...props.picker}
          size={props.size}
          disabled={props.disabled}
          onChange={props.onChange}
        />
      </Fragment>
    )
  }

  return (
    <ContentPlaceholder
      icon="fa fa-map-marker-alt"
      title={trans('no_location')}
      size={props.size}
    >
      <LocationsButton
        {...props.picker}
        size={props.size}
        disabled={props.disabled}
        onChange={props.onChange}
      />
    </ContentPlaceholder>
  )
}

implementPropTypes(LocationInput, DataInputTypes, {
  value: T.shape(LocationTypes.propTypes),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null
})

export {
  LocationInput
}
