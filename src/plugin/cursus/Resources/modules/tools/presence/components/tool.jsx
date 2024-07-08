import React from 'react'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {withReducer} from '#/main/app/store/reducer'
import {Routes} from '#/main/app/router'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {Tool, ToolPage} from '#/main/core/tool'

import {actions, reducer, selectors} from '#/plugin/cursus/tools/presence/store'
import {SignPresence} from '#/plugin/cursus/tools/presence/components/signing'
import {EventPresence} from '#/plugin/cursus/tools/presence/components/event'

const PresenceToolComponent = (props) =>
  <Tool
    {...omit(props, 'currentUser', 'getEventByCode')}
  >
    <ToolPage root={true}>
      <Routes
        path={props.path}
        routes={[
          {
            path: '/:code',
            onEnter: (params) => props.getEventByCode(params.code),
            component: SignPresence
          }, {
            path: '/',
            onEnter: () => props.resetEvent(),
            component: EventPresence
          }
        ]}
      />
    </ToolPage>
  </Tool>

const PresenceTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state)
    }),
    (dispatch) => ({
      getEventByCode(code = null) {
        dispatch(actions.getEventByCode(code))
      },
      resetEvent() {
        dispatch(actions.setCode(''))
        dispatch(actions.setCurrentEvent(null))
        dispatch(actions.setEventLoaded(false))
      }
    })
  )(PresenceToolComponent)
)

export {
  PresenceTool
}
