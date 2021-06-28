import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {MaterialMain} from '#/plugin/booking/tools/booking/material/containers/main'
import {RoomMain} from '#/plugin/booking/tools/booking/room/containers/main'

const BookingTool = (props) =>
  <Routes
    path={props.path}
    redirect={[
      {from: '/', exact: true, to: '/materials'}
    ]}
    routes={[
      {
        path: '/materials',
        component: MaterialMain
      }, {
        path: '/rooms',
        component: RoomMain
      }
    ]}
  />

BookingTool.propTypes = {
  path: T.string.isRequired
}

export {
  BookingTool
}
