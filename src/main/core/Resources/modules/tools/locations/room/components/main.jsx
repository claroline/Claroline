import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {RoomList} from '#/main/core/tools/locations/room/containers/list'
import {RoomDetails} from '#/main/core/tools/locations/room/containers/details'

const RoomMain = (props) =>
  <Routes
    path={props.path+'/rooms'}
    routes={[
      {
        path: '/',
        exact: true,
        component: RoomList
      }, {
        path: '/:id',
        onEnter: (params = {}) => props.open(params.id),
        component: RoomDetails
      }
    ]}
  />

RoomMain.propTypes = {
  path: T.string.isRequired,
  open: T.func.isRequired
}

export {
  RoomMain
}
