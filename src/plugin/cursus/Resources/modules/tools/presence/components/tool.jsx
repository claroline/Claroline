import React from 'react'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {Tool, ToolPage} from '#/main/core/tool'
import {withReducer} from '#/main/app/store/reducer'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {EventPresence} from '#/plugin/cursus/presence/components/event'
import {SignPresence} from '#/plugin/cursus/presence/components/signing'
import {actions, reducer, selectors} from '#/plugin/cursus/presence/store'
import {trans} from '#/main/app/intl'
import {PageContent} from '#/main/app/page'

const PresenceTool = (props) =>
  <Tool
    {...omit(props, 'currentUser', 'getEventByCode')}
    pages={[
      {
        path: '/:code',
        render: (routerProps) => (
          <ToolPage title={trans('presence', {}, 'tools')}>
            <PageContent>
              <SignPresence code={routerProps.match.params.code} path={props.path} />
            </PageContent>
          </ToolPage>
        )
      }, {
        path: '/',
        render: () => (
          <ToolPage title={trans('presence', {}, 'tools')}>
            <PageContent>
              <EventPresence path={props.path}/>
            </PageContent>
          </ToolPage>
        )
      }
    ]}
  />

export {
  PresenceTool
}
