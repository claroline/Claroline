import React from 'react'

import {Tool} from '#/main/core/tool'

import {ExampleComponents} from '#/main/example/tools/example/components/components'
import {CrudMain} from '#/main/example/tools/example/crud/containers/main'
import {ExampleForm} from '#/main/example/tools/example/components/form'
import {LINK_BUTTON} from '#/main/app/buttons'

const ExampleTool = (props) =>
  <Tool
    {...props}
    redirect={[
      {from: '/', to: '/components', exact: true}
    ]}
    menu={[
      {
        name: 'crud',
        type: LINK_BUTTON,
        label: 'Simple CRUD',
        target: props.path+'/crud'
      }, {
        name: 'forms',
        type: LINK_BUTTON,
        label: 'Forms',
        target: props.path+'/forms'
      }, {
        name: 'components',
        type: LINK_BUTTON,
        label: 'Components',
        target: props.path+'/components'
      }
    ]}
    pages={[
      {
        path: '/crud',
        component: CrudMain
      }, {
        path: '/forms',
        component: ExampleForm
      }, {
        path: '/components',
        render: () => <ExampleComponents path={props.path} />
      }
    ]}
  />

export {
  ExampleTool
}
