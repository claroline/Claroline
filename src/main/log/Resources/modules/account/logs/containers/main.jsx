import {withReducer} from '#/main/app/store/components/withReducer'

import {reducer, selectors} from '#/main/log/account/logs/store'
import {LogsMain as LogsMainComponent}  from '#/main/log/account/logs/components/main'

const LogsMain = withReducer(selectors.STORE_NAME, reducer)(
  LogsMainComponent
)

export {
  LogsMain
}
