import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {LogTool as LogToolComponent} from '#/main/log/administration/logs/components/tool'

const LogsTool = connect(
  (state) => ({
    isAdmin: securitySelectors.isAdmin(state)
  })
)(LogToolComponent)

export {
  LogsTool
}