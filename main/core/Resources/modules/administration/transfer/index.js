import {bootstrap} from '#/main/app/bootstrap'

// reducers
import {reducer} from '#/main/core/administration/transfer/reducer'
import {TransferTool} from '#/main/core/administration/transfer/components/tool'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.transfer-container',

  // app main component
  TransferTool,

  // app store configuration
  reducer
)
