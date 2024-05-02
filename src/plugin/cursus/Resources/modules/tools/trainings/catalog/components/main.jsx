import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {route} from '#/plugin/cursus/routing'
import {Course} from '#/plugin/cursus/course/containers/main'
import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {CatalogEdit} from '#/plugin/cursus/tools/trainings/catalog/containers/edit'
import {CatalogList} from '#/plugin/cursus/tools/trainings/catalog/components/list'
import {CatalogCreation} from '#/plugin/cursus/tools/trainings/catalog/components/creation'

const CatalogMain = (props) =>
  <Routes
    path={`${props.path}/catalog`}
    routes={[
      {
        path: '/',
        exact: true,
        render: () => (
          <CatalogList path={props.path} canEdit={props.canEdit} />
        )
      }, {
        path: '/new',
        onEnter: () => props.openForm(null, CourseTypes.defaultProps),
        disabled: !props.canEdit,
        render: () => (
          <CatalogCreation path={props.path} />
        )
      }, {
        path: '/:slug/edit',
        onEnter: (params = {}) => props.openForm(params.slug),
        component: CatalogEdit
      }, {
        path: '/:slug',
        onEnter: (params = {}) => props.open(params.slug),
        render: (params = {}) => (
          <Course
            path={props.course ? route(props.course) : ''}
            slug={params.slug}
            history={params.history}
          />
        )
      }
    ]}
  />

CatalogMain.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired,
  open: T.func.isRequired,
  openForm: T.func.isRequired
}

export {
  CatalogMain
}
