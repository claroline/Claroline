import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {locationTypes} from '#/main/core/administration/user/location/constants'
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
      type: 'string',
      label: trans('address'),
      render: (rowData) => getReadableAddress(rowData),
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

function getReadableAddress(location) {
  //this depends on the language I guess... but we don't always have every field either
  //basic display for now
  let str = ''
  let prepend = false

  if (location.street_number) {
    str += location.street_number
    prepend = true
  }

  if (location.street) {
    if (prepend) {
      str += ', '
    }
    str += location.street
    prepend = true
  }

  if (location.pc) {
    if (prepend) {
      str += ', '
    }
    str += location.pc
    prepend = true
  }

  if (location.town) {
    if (prepend) {
      str += ', '
    }
    str += location.town
    prepend = true
  }

  if (location.country) {
    if (prepend) {
      str += ', '
    }
    str += location.country
  }

  return str
}

export {
  LocationList
}
