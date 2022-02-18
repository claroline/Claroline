import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {ImportMain} from '#/main/transfer/tools/transfer/import/containers/main'
import {ExportMain} from '#/main/transfer/tools/transfer/export/containers/main'

const TransferTool = (props) =>
  <Routes
    path={props.path}
    redirect={[
      {from: '/', exact: true, to: '/import'}
    ]}
    routes={[
      {
        path: '/import',
        component: ImportMain
      }, {
        path: '/export',
        component: ExportMain
      }
    ]}
  />

TransferTool.propTypes = {
  path: T.string.isRequired
}

export {
  TransferTool
}
