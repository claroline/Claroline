import React from 'react'

import {Router, Routes} from '#/main/app/router'

import {MainView} from './main-view.jsx'
import {CompetencyView} from './competency-view.jsx'

const MyObjectivesTool = () =>
  <Router>
    <Routes
      routes={[
        {path: '/', component: MainView, exact: true},
        {path: '/:oId/competency/:cId', component: CompetencyView}
      ]}
    />
  </Router>

export {
  MyObjectivesTool
}
