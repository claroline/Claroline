import React from 'react'

import {Tool} from '#/main/core/tool'
import {route} from '#/plugin/cursus/routing'
import {Course} from '#/plugin/cursus/course/containers/main'
import {CatalogList} from '#/plugin/cursus/tools/catalog/components/list'

const CatalogTool = (props) =>
  <Tool
    {...props}
    pages={[
      {
        path: '/',
        exact: true,
        render: () => (
          <CatalogList path={props.path} canEdit={props.canEdit} />
        )
      }, {
        path: '/course/:slug',
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

export {
  CatalogTool
}
