import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {ExampleComponents} from '#/main/example/tools/example/components/components'
import {CrudMain} from '#/main/example/tools/example/crud/containers/main'
import {ExampleForm} from '#/main/example/tools/example/components/form'
import {Tool} from '#/main/core/tool'

const ExampleTool = (props) =>
  <Tool {...props}>
    <Routes
      path={props.path}
      redirect={[
        {from: '/', to: '/components', exact: true}
      ]}
      routes={[
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
  </Tool>

ExampleTool.propTypes = {
  path: T.string.isRequired
}

export {
  ExampleTool
}
