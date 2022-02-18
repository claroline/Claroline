import {reducer} from '#/main/transfer/tools/transfer/store'
import {TransferTool} from '#/main/transfer/tools/transfer/containers/tool'
import {TransferMenu} from '#/main/transfer/tools/transfer/components/menu'

/**
 * Transfer tool application.
 */
export default {
  component: TransferTool,
  menu: TransferMenu,
  store: reducer,
  styles: ['claroline-distribution-main-transfer-transfer-tool']
}
