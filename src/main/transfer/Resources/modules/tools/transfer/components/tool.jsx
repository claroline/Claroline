import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Tool} from '#/main/core/tool'

import {ImportMain} from '#/main/transfer/tools/transfer/import/containers/main'
import {ExportMain} from '#/main/transfer/tools/transfer/export/containers/main'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

const TransferTool = (props) =>
  <Tool
    {...props}
    styles={['claroline-distribution-main-transfer-transfer-tool']}
    redirect={[
      {from: '/', exact: true, to: '/import'}
    ]}
    menu={[
      {
        name: 'import-list',
        type: LINK_BUTTON,
        label: trans('all_imports', {}, 'transfer'),
        target: `${props.path}/import/history`
      }, {
        name: 'import',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('new_import', {}, 'transfer'),
        target: `${props.path}/import/new`,
        displayed: props.canImport
      }, {
        name: 'export-list',
        type: LINK_BUTTON,
        label: trans('all_exports', {}, 'transfer'),
        target: `${props.path}/export/history`
      }, {
        name: 'export',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('new_export', {}, 'transfer'),
        target: `${props.path}/export/new`,
        displayed: props.canExport
      }
    ]}
    pages={[
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
