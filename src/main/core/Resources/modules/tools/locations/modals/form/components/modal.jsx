import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {FormModal} from '#/main/app/data/modals/form/components/modal'

import {Location as LocationTypes} from '#/main/core/data/types/location/prop-types'

const LocationFormModal = (props) =>
  <FormModal
    {...omit(props, 'location')}
    name="locationForm"
    title={trans(props.isNew ? 'new_location' : 'location', {}, 'location')}
    subtitle={props.isNew ? trans('new_location_desc', {}, 'location') : undefined}
    target={props.isNew ?
      ['apiv2_location_create'] :
      ['apiv2_location_update', {id: props.location.id}]
    }
    data={props.location}
    saveLabel={trans(props.isNew ? 'add_location' : 'save_location', {}, 'actions')}
    definition={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'poster',
            type: 'poster',
            label: trans('poster'),
            hideLabel: true
          }, {
            name: 'name',
            type: 'string',
            label: trans('name'),
            required: true
          }, {
            name: 'meta.description',
            type: 'html',
            label: trans('description')
          }
        ]
      }, {
        title: trans('contact_information'),
        primary: true,
        hideTitle: true,
        fields: [
          {
            name: 'phone',
            type: 'string',
            label: trans('phone')
          }, {
            name: 'address',
            type: 'address',
            label: trans('address')
          }
        ]
      }
    ]}
  />

LocationFormModal.propTypes = {
  isNew: T.bool,
  location: T.shape(
    LocationTypes.propTypes
  ),
  onSave: T.func
}

export {
  LocationFormModal
}
