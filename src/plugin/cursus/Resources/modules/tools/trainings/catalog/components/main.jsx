import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {CatalogList} from '#/plugin/cursus/tools/trainings/catalog/components/list'
import {CatalogDetails} from '#/plugin/cursus/tools/trainings/catalog/containers/details'
import {CatalogCreation} from '#/plugin/cursus/tools/trainings/catalog/components/creation'
import {CatalogEdit} from '#/plugin/cursus/tools/trainings/catalog/containers/edit'

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
        component: CatalogDetails
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
