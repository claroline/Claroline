import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {TemplateList} from '#/main/core/administration/template/containers/list'
import {TemplateDetails} from '#/main/core/administration/template/containers/details'

const TemplateTool = (props) =>
  <Routes
    path={props.path}
    redirect={[
      {from: '/', exact: true, to: '/email'}
    ]}
    routes={[
      {
        path: '/:type',
        exact: true,
        render: (routerProps) => (
          <TemplateList
            type={routerProps.match.params.type}
          />
        )
      }, {
        path: '/:type/:id',
        onEnter: (params) => props.open(params.id || null),
        component: TemplateDetails
      }
    ]}
  />

TemplateTool.propTypes = {
  path: T.string.isRequired,
  open: T.func.isRequired
}

export {
  TemplateTool
}
