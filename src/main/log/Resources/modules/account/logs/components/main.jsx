import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

import {AccountPage} from '#/main/app/account/containers/page'
import {route} from '#/main/app/account/routing'

import {FunctionalLogList} from '#/main/log/account/logs/components/functional'
import {SecurityLogList} from '#/main/log/account/logs/components/security'

const LogsMain = () =>
  <AccountPage
    title={trans('logs', {}, 'tools')}
  >
    <div className="row">
      <div className="col-md-3">
        <Vertical
          basePath={route('logs')}
          tabs={[
            {
              title: trans('functional', {}, 'log'),
              path: '/functional'
            }, {
              title: trans('security', {}, 'log'),
              path: '/security'
            }
          ]}
        />
      </div>

      <div className="col-md-9">
        <Routes
          path={route('logs')}
          redirect={[
            {from: '/', exact: true, to: '/functional'}
          ]}
          routes={[
            {
              path: '/functional',
              component: FunctionalLogList
            }, {
              path: '/security',
              component: SecurityLogList
            }
          ]}
        />
      </div>
    </div>
  </AccountPage>

export {
  LogsMain
}
