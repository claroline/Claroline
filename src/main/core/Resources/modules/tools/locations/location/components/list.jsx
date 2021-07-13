import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {ToolPage} from '#/main/core/tool/containers/page'

import {selectors as baseSelectors} from '#/main/core/tools/locations/store'
import {LocationCard} from '#/main/core/data/types/location/components/card'

const LocationList = props =>
  <ToolPage
    path={[
      {
        type: LINK_BUTTON,
        label: trans('locations', {}, 'tools'),
        target: `${props.path}/locations`
      }
    ]}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_location'),
        target: `${props.path}/locations/new`,
        primary: true
      }
    ]}
  >
    <ListData
      name={`${baseSelectors.STORE_NAME}.locations.list`}
      fetch={{
        url: ['apiv2_location_list'],
        autoload: true
      }}
      definition={[
        {
          name: 'name',
          type: 'string',
          label: trans('name'),
          displayed: true,
          primary: true
        }, {
          name: 'address',
          type: 'address',
          label: trans('address'),
          displayed: true
        }, {
          name: 'phone',
          type: 'string',
          label: trans('phone'),
          displayed: true
        }, {
          name: 'coordinates',
          type: 'string',
          label: trans('coordinates'),
          filterable: false,
          render: (location) => {
            if (location.gps.latitude && location.gps.longitude) {
              return location.gps.latitude + ' - ' + location.gps.longitude
            }
          }
        }
      ]}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        target: `${props.path}/locations/${row.id}`,
        label: trans('edit', {}, 'actions')
      })}
      delete={{
        url: ['apiv2_location_delete_bulk']
      }}
      actions={(rows) => [
        {
          name: 'edit',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          target: `${props.path}/locations/${rows[0].id}/edit`,
          group: trans('management'),
          scope: ['object']
        }, {
          name: 'geolocate',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-map-marker-alt',
          label: trans('geolocate', {}, 'actions'),
          callback: () => props.geolocate(rows[0]),
          scope: ['object'], // todo should be available in selection mode too
          group: trans('management')
        }
      ]}
      card={LocationCard}
    />
  </ToolPage>

LocationList.propTypes = {
  path: T.string.isRequired,
  geolocate: T.func.isRequired
}

export {
  LocationList
}
