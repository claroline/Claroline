import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {CatalogMain} from '#/plugin/cursus/tools/trainings/catalog/containers/main'
import {SessionMain} from '#/plugin/cursus/tools/trainings/session/containers/main'
import {EventMain} from '#/plugin/cursus/tools/trainings/event/containers/main'
import {QuotaMain} from '#/plugin/cursus/tools/trainings/quota/containers/main'

const TrainingsTool = (props) =>
  <Routes
    path={props.path}
    redirect={[
      {from: '/', exact: true, to: '/catalog'}
    ]}
    routes={[
      {
        path: '/catalog',
        component: CatalogMain
      }, {
        path: '/registered',
        component: SessionMain
      }, {
        path: '/events',
        component: EventMain
      }, {
        path: '/quotas',
        component: QuotaMain
      }
    ]}
  />

TrainingsTool.propTypes = {
  path: T.string.isRequired
}

export {
  TrainingsTool
}