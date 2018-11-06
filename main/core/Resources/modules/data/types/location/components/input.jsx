import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'

import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {LocationCard} from '#/main/core/user/data/components/location-card'
import {Location as LocationType} from '#/main/core/user/prop-types'
import {MODAL_LOCATIONS_PICKER} from '#/main/core/modals/locations'

const LocationInput = props => {
  if (props.value) {
    return(
      <div>
        <LocationCard
          data={props.value}
          size="sm"
          orientation="col"
          actions={[
            {
              name: 'delete',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash-o',
              label: trans('delete', {}, 'actions'),
              dangerous: true,
              callback: () => props.picker.handleSelect ?
                props.onChange(props.picker.handleSelect(null)) :
                props.onChange(null)
            }
          ]}
        />
        <ModalButton
          className="btn btn-locations-primary"
          style={{marginTop: 10}}
          primary={true}
          modal={[MODAL_LOCATIONS_PICKER, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => props.picker.handleSelect ?
                props.onChange(props.picker.handleSelect(selected[0])) :
                props.onChange(selected[0])
            })
          }]}
        >
          <span className="fa fa-fw fa-location-arrow icon-with-text-right" />
          {trans('select_a_location')}
        </ModalButton>
      </div>
    )
  } else {
    return (
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-location-arrow"
        title={trans('no_location')}
      >
        <ModalButton
          className="btn btn-locations-primary"
          primary={true}
          modal={[MODAL_LOCATIONS_PICKER, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => props.picker.handleSelect ?
                props.onChange(props.picker.handleSelect(selected[0])) :
                props.onChange(selected[0])
            })
          }]}
        >
          <span className="fa fa-fw fa-location-arrow icon-with-text-right" />
          {trans('select_a_location')}
        </ModalButton>
      </EmptyPlaceholder>
    )
  }
}

implementPropTypes(LocationInput, FormFieldTypes, {
  value: T.shape(LocationType.propTypes),
  picker: T.shape({
    title: T.string,
    confirmText: T.string,
    handleSelect: T.func
  })
}, {
  value: null,
  picker: {
    title: trans('location_selector'),
    confirmText: trans('select', {}, 'actions')
  }
})

export {
  LocationInput
}
