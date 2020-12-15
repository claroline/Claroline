import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {locationTypes} from '#/main/core/administration/community/location/constants'
import {LocationCard} from '#/main/core/user/data/components/location-card'

const LocationList = {
  open: (row) => ({
    type: LINK_BUTTON,
    target: `/locations/form/${row.id}`,
    label: trans('edit', {}, 'actions')
  }),
  definition: [
    {
      name: 'name',
      type: 'string',
      label: trans('name'),
      displayed: true,
      primary: true
    }, {
      name: 'meta.type',
      type: 'choice',
      label: trans('type'),
      options: {
        choices: locationTypes
      }
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
      render: (rowData) => getCoordinates(rowData)
    }
  ],
  card: LocationCard
}


function getCoordinates(location) {
  if (location.gps.latitude && location.gps.longitude) {
    return location.gps.latitude + ' - ' + location.gps.longitude
  }
}

export {
  LocationList
}
