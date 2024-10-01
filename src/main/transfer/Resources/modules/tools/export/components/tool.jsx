import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Tool} from '#/main/core/tool'

import {ExportList} from '#/main/transfer/tools/export/containers/list'
import {ExportDetails} from '#/main/transfer/tools/export/containers/details'
import {ExportEditor} from '#/main/transfer/tools/export/editor/containers/main'

const ExportTool = (props) =>
  <Tool
    {...props}
    styles={['claroline-distribution-main-transfer-transfer-tool']}
    pages={[
      {
        path: '/',
        exact: true,
        component: ExportList
      }, {
        path: '/new',
        disabled: !props.canExport,
        render: () => (<ExportEditor path={props.path} isNew={true}/>)
      }, {
        path: '/:id',
        onEnter: (params) => props.open(params.id),
        component: ExportDetails
      }
    ]}
  />

ExportTool.propTypes = {
  path: T.string.isRequired,
  canExport: T.bool.isRequired,
  open: T.func.isRequired
}

export {
  ExportTool
}
