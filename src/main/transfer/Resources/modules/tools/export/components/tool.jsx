import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Tool} from '#/main/core/tool'

import {ExportList} from '#/main/transfer/tools/export/containers/list'
import {ExportEditor} from '#/main/transfer/export/editor/containers/main'
import {ExportDetails} from '#/main/transfer/tools/export/containers/details'

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
        render: () => (
          <ExportEditor
            isNew={true}
            path={props.path}
            contextData={props.contextData}
          />
        )
      }, {
        path: '/:id',
        onEnter: (params) => props.open(params.id),
        component: ExportDetails
      }
    ]}
  />

ExportTool.propTypes = {
  contextData: T.object,
  open: T.func.isRequired,
  path: T.string.isRequired,
  canExport: T.bool.isRequired
}

export {
  ExportTool
}
