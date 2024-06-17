import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'

import {selectors} from '#/main/core/tools/locations//store'

const LocationForm = props =>
  <FormData
    level={3}
    name={`${selectors.STORE_NAME}.current`}
    buttons={true}
    target={(location, isNew) => isNew ?
      ['apiv2_location_create'] :
      ['apiv2_location_update', {id: location.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    definition={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            required: true
          }
        ]
      }, {
        title: trans('information'),
        icon: 'fa fa-fw fa-circle-info',
        fields: [
          {
            name: 'meta.description',
            type: 'html',
            label: trans('description')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'poster',
            type: 'image',
            label: trans('poster')
          }, {
            name: 'thumbnail',
            type: 'image',
            label: trans('thumbnail')
          }
        ]
      }, {
        title: trans('contact_information'),
        icon: 'fa fa-fw fa-id-card',
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

LocationForm.propTypes = {
  path: T.string.isRequired
}

export {
  LocationForm
}
