import {withReducer} from '#/main/app/store/components/withReducer'

import {TransferTool as TransferToolComponent} from '#/main/transfer/tools/transfer/components/tool'
import {reducer as toolReducer, selectors as toolSelectors} from '#/main/core/tool/store'

const TransferTool = withReducer(toolSelectors.STORE_NAME, toolReducer)(TransferToolComponent)

export {
  TransferTool
}
