import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Tool} from '#/main/core/tool'

import {LocationList} from '#/main/core/tools/locations/components/list'
import {LocationShow} from '#/main/core/tools/locations/components/show'

const LocationsTool = (props) =>
  <Tool
    {...props}
    pages={[
      {
        path: '/',
        exact: true,
        component: LocationList
      }, {
        path: '/:id',
        component: LocationShow,
        onEnter: (params) => props.openLocation(params.id)
      }
    ]}
  />

LocationsTool.propTypes = {
  openLocation: T.func.isRequired
}

export {
  LocationsTool
}
