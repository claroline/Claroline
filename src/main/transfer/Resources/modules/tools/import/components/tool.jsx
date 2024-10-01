import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Tool} from '#/main/core/tool'

import {ImportList} from '#/main/transfer/tools/import/containers/list'
import {ImportEditor} from '#/main/transfer/import/editor/containers/main'
import {ImportDetails} from '#/main/transfer/tools/import/containers/details'

const ImportTool = (props) =>
  <Tool
    {...props}
    styles={['claroline-distribution-main-transfer-transfer-tool']}
    pages={[
      {
        path: '/',
        exact: true,
        component: ImportList
      }, {
        path: '/new',
        disabled: !props.canImport,
        render: () => (
          <ImportEditor
            isNew={true}
            path={props.path}
            contextData={props.contextData}
          />
        )
      }, {
        path: '/:id',
        onEnter: (params) => props.open(params.id),
        component: ImportDetails
      }
    ]}
  />

ImportTool.propTypes = {
  contextData: T.object,
  open: T.func.isRequired,
  path: T.string.isRequired,
  canImport: T.bool.isRequired
}

export {
  ImportTool
}
