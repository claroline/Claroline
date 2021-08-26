import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {CatalogList} from '#/plugin/cursus/tools/trainings/catalog/components/list'
import {CatalogDetails} from '#/plugin/cursus/tools/trainings/catalog/containers/details'
import {CatalogForm} from '#/plugin/cursus/tools/trainings/catalog/containers/form'

const CatalogMain = (props) =>
  <Routes
    path={`${props.path}/catalog`}
    routes={[
      {
        path: '/',
        exact: true,
        render: () => (
          <CatalogList path={`${props.path}/catalog`} canEdit={props.canEdit} />
        )
      }, {
        path: '/new',
        exact: true,
        onEnter: () => props.openForm(null, CourseTypes.defaultProps),
        disabled: !props.canEdit,
        render: () => (
          <CatalogForm path={`${props.path}/catalog`} />
        )
      }, {
        path: '/:slug/edit',
        onEnter: (params = {}) => props.openForm(params.slug),
        disabled: !props.canEdit,
        render: () => (
          <CatalogForm path={`${props.path}/catalog`} />
        )
      }, {
        path: '/:slug',
        onEnter: (params = {}) => props.open(params.slug),
        render: () => (
          <CatalogDetails path={`${props.path}/catalog`} />
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
