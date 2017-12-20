import React from 'react'

import {locationTypes} from '#/main/core/administration/user/location/constants'

const LocationCard = (row) => ({
  icon: 'fa fa-location-arrow',
  title: row.name,
  subtitle: locationTypes[row.meta.type],
  contentText: '', // todo display address
  footer: <span>footer</span> // todo display coords
})

export {
  LocationCard
}
