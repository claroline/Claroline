import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {LocationDetails}  from '#/main/core/tools/locations/location/containers/details'
import {LocationList} from '#/main/core/tools/locations/location/containers/list'
import {LocationNew} from '#/main/core/tools/locations/location/containers/new'

const LocationMain = props =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/locations',
        exact: true,
        component: LocationList
      }, {
        path: '/locations/new',
        component: LocationNew,
        onEnter: () => props.open(null)
      }, {
        path: '/locations/:id',
        component: LocationDetails,
        onEnter: (params) => props.open(params.id)
      }
    ]}
  />

LocationMain.propTypes = {
  path: T.string.isRequired,
  open: T.func.isRequired
}

export {
  LocationMain
}
