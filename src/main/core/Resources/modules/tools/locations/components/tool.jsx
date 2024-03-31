import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Tool} from '#/main/core/tool'

import {LocationMain} from '#/main/core/tools/locations/location/containers/main'
import {MaterialMain} from '#/main/core/tools/locations/material/containers/main'
import {RoomMain} from '#/main/core/tools/locations/room/containers/main'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

const LocationsTool = (props) =>
  <Tool
    {...props}
    redirect={[
      {from: '/', exact: true, to: '/locations'}
    ]}
    menu={[
      {
        name: 'locations',
        type: LINK_BUTTON,
        label: trans('locations'),
        target: `${props.path}/locations`
      }, {
        name: 'materials',
        type: LINK_BUTTON,
        label: trans('materials', {}, 'location'),
        target: `${props.path}/materials`
      }, {
        name: 'rooms',
        type: LINK_BUTTON,
        label: trans('rooms', {}, 'location'),
        target: `${props.path}/rooms`
      }
    ]}
    pages={[
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
