import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Tool} from '#/main/core/tool'

import {LocationList} from '#/main/core/tools/locations/containers/list'
import {LocationNew} from '#/main/core/tools/locations/containers/new'
import {LocationDetails} from '#/main/core/tools/locations/containers/details'

const LocationsTool = (props) =>
  <Tool
    {...props}
    pages={[
      {
        path: '/',
        exact: true,
        component: LocationList
      }, {
        path: '/new',
        component: LocationNew,
        onEnter: () => props.open(null)
      }, {
        path: '/:id',
        component: LocationDetails,
        onEnter: (params) => props.open(params.id)
      }
    ]}
  />

LocationsTool.propTypes = {
  open: T.func.isRequired
}

export {
  LocationsTool
}
