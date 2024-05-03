import React from 'react'
import {connect} from 'react-redux'

import {Routes} from '#/main/app/router'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {ToolPage} from '#/main/core/tool'

import {actions, reducer, selectors} from '#/plugin/cursus/tools/presence/store'
import {SignPresence} from '#/plugin/cursus/tools/presence/components/signing'
import {EventPresence} from '#/plugin/cursus/tools/presence/components/event'

const PresenceToolComponent = (props) =>
  <ToolPage>
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

const PresenceTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state)
    }),
    (dispatch) => ({
      getEventByCode(code = null) {
        dispatch(actions.getEventByCode(code))
      }
    })
  )(PresenceToolComponent)
)

export {
  PresenceTool
}
