import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {TransferMenu as TransferMenuComponent} from '#/main/transfer/tools/transfer/components/menu'

const TransferMenu = connect(
  (state) => ({
    canImport: hasPermission('import', toolSelectors.toolData(state)),
    canExport: hasPermission('export', toolSelectors.toolData(state))
  })
)(TransferMenuComponent)

export {
  TransferMenu
}
