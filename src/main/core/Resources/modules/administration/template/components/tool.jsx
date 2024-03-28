import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {TemplateList} from '#/main/core/administration/template/containers/list'
import {TemplateDetails} from '#/main/core/administration/template/containers/details'
import {Tool} from '#/main/core/tool'

const TemplateTool = (props) =>
  <Tool
    {...props}
  >
    <Routes
      path={props.path}
      redirect={[
        {from: '/', exact: true, to: '/email'}
      ]}
      routes={[
        {
          path: '/:type',
          exact: true,
          onEnter: () => props.invalidateList(),
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
  </Tool>

TemplateTool.propTypes = {
  path: T.string.isRequired,
  open: T.func.isRequired,
  invalidateList: T.func.isRequired
}

export {
  TemplateTool
}
