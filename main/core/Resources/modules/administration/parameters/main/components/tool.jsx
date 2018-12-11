import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {Vertical} from '#/main/app/content/tabs/components/vertical'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Home} from '#/main/core/administration/parameters/main/components/home'
import {Meta} from '#/main/core/administration/parameters/main/components/meta'
import {I18n} from '#/main/core/administration/parameters/main/components/i18n'
import {Plugins} from '#/main/core/administration/parameters/main/components/plugins'
import {Maintenance} from '#/main/core/administration/parameters/main/components/maintenance'

const Tool = () =>
  <ToolPage
    styles={['claroline-distribution-main-core-administration-parameters']}
  >
    <div className="row">
      <div className="col-md-3">
        <Vertical
          style={{
            marginTop: '20px' // FIXME
          }}
          tabs={[
            {
              icon: 'fa fa-fw fa-info',
              title: trans('information'),
              path: '/',
              exact: true
            }, {
              icon: 'fa fa-fw fa-home',
              title: trans('home'),
              path: '/home'
            }, {
              icon: 'fa fa-fw fa-language',
              title: trans('language'),
              path: '/i18n'
            }, {
              icon: 'fa fa-fw fa-cubes',
              title: trans('plugins'),
              path: '/plugins'
            }, {
              icon: 'fa fa-fw fa-wrench',
              title: trans('maintenance'),
              path: '/maintenance'
            }
          ]}
        />
      </div>

      <div className="col-md-9">
        <Routes
          routes={[
            {
              path: '/',
              exact: true,
              component: Meta
            }, {
              path: '/home',
              exact: true,
              component: Home
            }, {
              path: '/i18n',
              exact: true,
              component: I18n
            }, {
              path: '/plugins',
              exact: true,
              component: Plugins
            }, {
              path: '/maintenance',
              exact: true,
              component: Maintenance
            }
          ]}
        />
      </div>
    </div>
  </ToolPage>

export {
  Tool
}
