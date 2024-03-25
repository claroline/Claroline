import React from 'react'
import {connect} from 'react-redux'

import {Routes} from '#/main/app/router'
import {ToolPage} from '#/main/core/tool/containers/page'
import {SignPresence} from '#/plugin/cursus/tools/presence/components/signing'
import {EventPresence} from '#/plugin/cursus/tools/presence/components/event'
import {actions} from '#/plugin/cursus/tools/presence/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

const PresenceToolComponent = (props) =>
  <ToolPage className="presence-tool">
    <Routes
      path={props.path}
      routes={[
        {
          path: '/:code',
          onEnter: (params) => props.getEventByCode(params.code),
          component: SignPresence
        }, {
          path: '/',
          component: EventPresence
        }
      ]}
    />
  </ToolPage>

const PresenceTool = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  }),
  (dispatch) => ({
    getEventByCode(code = null) {
      dispatch(actions.getEventByCode(code))
    }
  })
)(PresenceToolComponent)

export {
  PresenceTool
}
