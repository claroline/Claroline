import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {CatalogMain} from '#/plugin/cursus/tools/cursus/catalog/containers/main'

const CursusTool = (props) =>
  <Routes
    path={props.path}
    redirect={[
      {from: '/', exact: true, to: '/catalog'}
    ]}
    routes={[
      {
        path: '/catalog',
        component: CatalogMain
      }
    ]}
  />

CursusTool.propTypes = {
  path: T.string.isRequired
}

export {
  CursusTool
}