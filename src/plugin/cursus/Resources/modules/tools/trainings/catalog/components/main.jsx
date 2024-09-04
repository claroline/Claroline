import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {route} from '#/plugin/cursus/routing'
import {Course} from '#/plugin/cursus/course/containers/main'
import {CourseEditor} from '#/plugin/cursus/course/editor/containers/main'
import {CatalogList} from '#/plugin/cursus/tools/trainings/catalog/components/list'
import {CourseCreation} from '#/plugin/cursus/course/components/creation'

const CatalogMain = (props) =>
  <Routes
    path={props.path+'/course'}
    routes={[
      {
        path: '/',
        exact: true,
        render: () => (
          <CatalogList
            path={props.path}
            canEdit={props.canEdit}
            contextType={props.contextType}
            openForm={props.openForm}
          />
        )
      }, {
        path: '/new',
        disabled: !props.canEdit,
        component: CourseCreation
      }, {
        path: '/:slug/edit',
        onEnter: (params = {}) => props.openForm(params.slug),
        component: CourseEditor
      }, {
        path: '/:slug',
        onEnter: (params = {}) => props.open(params.slug),
        render: (params = {}) => (
          <Course
            path={props.course ? route(props.course, null, props.path) : ''}
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
