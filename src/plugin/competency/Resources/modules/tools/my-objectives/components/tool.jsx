import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {ToolPage} from '#/main/core/tool/containers/page'

import {MainView} from '#/plugin/competency/tools/my-objectives/components/main-view'
import {CompetencyView} from '#/plugin/competency/tools/my-objectives/components/competency-view'

const MyObjectivesTool = (props) =>
  <ToolPage>
    <Routes
      path={props.path}
      routes={[
        {
          path: '/',
          component: MainView,
          exact: true
        }, {
          path: '/:oId/competency/:cId',
          component: CompetencyView
        }
      ]}
    />
  </ToolPage>

MyObjectivesTool.propTypes = {
  path: T.string.isRequired
}

export {
  MyObjectivesTool
}
