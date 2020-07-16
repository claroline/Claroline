import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Course as CourseTypes} from '#/plugin/cursus/course/prop-types'
import {CatalogList} from '#/plugin/cursus/tools/cursus/catalog/components/list'
import {CatalogDetails} from '#/plugin/cursus/tools/cursus/catalog/containers/details'
import {CatalogForm} from '#/plugin/cursus/tools/cursus/catalog/containers/form'

const CatalogMain = (props) =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/catalog',
        exact: true,
        render: () => {
          const List = (
            <CatalogList path={props.path} />
          )

          return List
        }
      }, {
        path: '/catalog/new',
        component: CatalogForm,
        exact: true,
        onEnter: () => props.openForm(null, CourseTypes.defaultProps)
      }, {
        path: '/catalog/:slug/edit',
        component: CatalogForm,
        onEnter: (params = {}) => props.openForm(params.slug)
      }, {
        path: '/catalog/:slug',
        component: CatalogDetails,
        onEnter: (params = {}) => props.open(params.slug)
      }
    ]}
  />

CatalogMain.propTypes = {
  path: T.string.isRequired,
  open: T.func.isRequired,
  openForm: T.func.isRequired
}

export {
  CatalogMain
}
