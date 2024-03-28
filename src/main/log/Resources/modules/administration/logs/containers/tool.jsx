
import {withReducer} from '#/main/app/store/reducer'

import {LogsTool as LogsToolComponent} from '#/main/log/administration/logs/components/tool'
import {reducer, selectors} from '#/main/log/administration/logs/store'

const LogsTool = withReducer(selectors.STORE_NAME, reducer)(LogsToolComponent)

export {
  LogsTool
}
