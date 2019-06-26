import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

import {ToolPage} from '#/main/core/tool/containers/page'
import {Home} from '#/main/core/administration/parameters/main/components/home'
import {Archive} from '#/main/core/administration/parameters/main/components/archive'
import {Meta} from '#/main/core/administration/parameters/main/components/meta'
import {I18n} from '#/main/core/administration/parameters/main/components/i18n'
import {Plugins} from '#/main/core/administration/parameters/main/components/plugins'
import {Maintenance} from '#/main/core/administration/parameters/main/components/maintenance'
import {Messages} from '#/main/core/administration/parameters/main/components/messages'
import {Message} from '#/main/core/administration/parameters/main/components/message'

const Tool = (props) =>
  <ToolPage
    styles={['claroline-distribution-main-core-administration-parameters']}
    actions={'/messages' === props.location.pathname ? [
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_connection_message'),
        target: '/messages/form',
        primary: true
      }
    ] : []}
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
            }, {
              icon: 'fa fa-fw fa-book',
              title: trans('archive'),
              path: '/archive'
            }, {
              icon: 'fa fa-fw fa-comment-dots',
              title: trans('connection_messages'),
              path: '/messages'
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
            }, {
              path: '/archive',
              exact: true,
              component: Archive
            }, {
              path: '/messages',
              exact: true,
              component: Messages
            }, {
              path: '/messages/form/:id?',
              component: Message,
              onEnter: (params) => props.openConnectionMessageForm(params.id),
              onLeave: () => props.resetConnectionMessageFrom()
            }
          ]}
        />
      </div>
    </div>
  </ToolPage>

Tool.propTypes = {
  location: T.shape({
    pathname: T.string
  }),
  openConnectionMessageForm: T.func.isRequired,
  resetConnectionMessageFrom: T.func.isRequired
}

export {
  Tool
}
