import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const TransferMenu = (props) =>
  <ToolMenu
    actions={[
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
  />

TransferMenu.propTypes = {
  path: T.string,
  canImport: T.bool.isRequired,
  canExport: T.bool.isRequired
}

export {
  TransferMenu
}
