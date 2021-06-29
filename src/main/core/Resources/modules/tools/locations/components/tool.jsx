import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {LocationMain} from '#/main/core/tools/locations/location/containers/main'
import {MaterialMain} from '#/main/core/tools/locations/material/containers/main'
import {RoomMain} from '#/main/core/tools/locations/room/containers/main'

const LocationsTool = (props) =>
  <Routes
    path={props.path}
    redirect={[
      {from: '/', exact: true, to: '/locations'}
    ]}
    routes={[
      {
        path: '/locations',
        component: LocationMain
      }, {
        path: '/materials',
        component: MaterialMain
      }, {
        path: '/rooms',
        component: RoomMain
      }
    ]}
  />

LocationsTool.propTypes = {
  path: T.string.isRequired
}

export {
  LocationsTool
}
