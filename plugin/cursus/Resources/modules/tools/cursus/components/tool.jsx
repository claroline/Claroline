import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'

import {ToolPage} from '#/main/core/tool/containers/page'

import {SessionsCatalog} from '#/plugin/cursus/tools/cursus/catalog/session/components/sessions-catalog'

const CursusTool = (props) =>
  <ToolPage
    subtitle={
      <Routes
        path={props.path}
        routes={[
          {
            path: '/catalog',
            render: () => trans('catalog', {}, 'cursus')
          }
        ]}
      />
    }
  >
    <Routes
      path={props.path}
      redirect={[
        {from: '/', exact: true, to: '/catalog'}
      ]}
      routes={[
        {
          path: '/catalog',
          render: () => {
            const Catalog = (
              <SessionsCatalog path={props.path} />
            )

            return Catalog
          }
        }
      ]}
    />
  </ToolPage>

CursusTool.propTypes = {
  path: T.string.isRequired
}

export {
  CursusTool
}