import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {LogsTool as LogsToolComponent} from '#/main/log/administration/logs/components/tool'

const LogsTool = withRouter(
  connect()(LogsToolComponent)
)

export {
  LogsTool
}
