import {withReducer} from '#/main/app/store/reducer'

import {TransferTool as TransferToolComponent} from '#/main/transfer/tools/transfer/components/tool'
import {reducer, selectors} from '#/main/transfer/tools/transfer/store'

const TransferTool = withReducer(selectors.STORE_NAME, reducer)(TransferToolComponent)

export {
  TransferTool
}
