import {reducer} from '#/main/core/tools/transfer/store'
import {TransferTool} from '#/main/core/tools/transfer/containers/tool'
import {TransferMenu} from '#/main/core/tools/transfer/components/menu'

/**
 * Resources tool application.
 */
export default {
  component: TransferTool,
  menu: TransferMenu,
  store: reducer
}
