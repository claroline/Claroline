import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {ToolPage} from '#/main/core/tool'
import {ContentTabs} from '#/main/app/content/components/tabs'
import {LINK_BUTTON} from '#/main/app/buttons'

import {ExampleAlerts} from '#/main/example/tools/example/components/alerts'
import {ExampleButtons} from '#/main/example/tools/example/components/buttons'
import {ExampleNavs} from '#/main/example/tools/example/components/navs'
import {ExampleContent} from '#/main/example/tools/example/components/content'
import {ExampleUsers} from '#/main/example/tools/example/components/users'
import {ExampleProgression} from '#/main/example/tools/example/components/progression'

const ExampleComponents = (props) =>
  <ToolPage
    title="Components"
  >
    <ContentTabs
      className="mb-3"
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
          name: 'content',
          type: LINK_BUTTON,
          label: 'Content',
          target: props.path+'/components/content'
        }, {
          name: 'users',
          type: LINK_BUTTON,
          label: 'Users',
          target: props.path+'/components/users',
          displayed: false
        }, {
          name: 'progression',
          type: LINK_BUTTON,
          label: 'Progression',
          target: props.path+'/components/progression'
        }, {
          name: 'users',
          type: LINK_BUTTON,
          label: 'Users',
          target: props.path+'/components/users'
        }
      ]}
    />
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
          path: '/content',
          component: ExampleContent
        }, {
          path: '/users',
          component: ExampleUsers
        }, {
          path: '/progression',
          component: ExampleProgression
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
