import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Routes} from '#/main/app/router'

import {Tab as TabTypes} from '#/plugin/home/prop-types'

import {CatalogList} from '#/plugin/cursus/home/catalog/components/list'
import {CatalogDetails} from '#/plugin/cursus/home/catalog/containers/details'

const CatalogTab = props =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/',
        exact: true,
        render: () => (
          <CatalogList
            path={props.path}
            {...omit(props, 'open')}
          />
        )
      }, {
        path: '/:slug',
        onEnter: (params = {}) => props.open(params.slug),
        render: () => (
          <CatalogDetails
            path={props.path}
            {...omit(props, 'open')}
          />
        )
      }
    ]}
  />

CatalogTab.propTypes = {
  path: T.string.isRequired,
  title: T.string.isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentTab: T.shape(
    TabTypes.propTypes
  ),
  open: T.func.isRequired,
  openForm: T.func.isRequired
}

export {
  CatalogTab
}
