import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ContentTabs} from '#/main/app/content/components/tabs'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

import {ExampleAlerts} from '#/main/example/tools/example/components/alerts'
import {ExampleButtons} from '#/main/example/tools/example/components/buttons'
import {ExampleNavs} from '#/main/example/tools/example/components/navs'
import {ExampleUsers} from '#/main/example/tools/example/components/users'

const ExampleComponents = (props) =>
  <ToolPage
    path={[
      {
        type: LINK_BUTTON,
        label: 'Components',
        target: props.path+'/components'
      }
    ]}
    subtitle="Components"
  >
    <header className="row content-heading">
      <ContentTabs
        sections={[
          {
            name: 'alerts',
            type: LINK_BUTTON,
            label: 'Alerts',
            target: props.path+'/components/alerts'
          }, {
            name: 'buttons',
            type: LINK_BUTTON,
            label: 'Buttons',
            target: props.path+'/components/buttons'
          }, {
            name: 'navs',
            type: LINK_BUTTON,
            label: 'Navs',
            target: props.path+'/components/navs'
          }, {
            name: 'users',
            type: LINK_BUTTON,
            label: 'Users',
            target: props.path+'/components/users'
          }
        ]}

        actions={[
          {
            name: 'primary',
            type: CALLBACK_BUTTON,
            label: 'Primary action',
            callback: () => true,
            primary: true
          }, {
            name: 'other-1',
            type: CALLBACK_BUTTON,
            label: 'Other action 1',
            callback: () => true,
            group: 'Group 1'
          }, {
            name: 'other-2',
            type: CALLBACK_BUTTON,
            label: 'Other action 2',
            callback: () => true,
            group: 'Group 1'
          }, {
            name: 'other-3',
            type: CALLBACK_BUTTON,
            label: 'Other action 3',
            callback: () => true,
            group: 'Group 2'
          }, {
            name: 'disabled',
            type: CALLBACK_BUTTON,
            label: 'Disabled action',
            callback: () => true,
            disabled: true,
            group: 'Group 2'
          }, {
            name: 'dangerous',
            type: CALLBACK_BUTTON,
            label: 'Dangerous action',
            callback: () => true,
            dangerous: true
          }
        ]}
      />
    </header>
    <Routes
      path={props.path+'/components'}
      redirect={[
        {from: '/', to: '/alerts', exact: true}
      ]}
      routes={[
        {
          path: '/alerts',
          component: ExampleAlerts
        }, {
          path: '/buttons',
          component: ExampleButtons
        }, {
          path: '/navs',
          render: () => <ExampleNavs path={props.path+'/components/navs'} />
        }, {
          path: '/users',
          component: ExampleUsers
        }
      ]}
    />
  </ToolPage>

ExampleComponents.propTypes = {
  path: T.string.isRequired
}

export {
  ExampleComponents
}
