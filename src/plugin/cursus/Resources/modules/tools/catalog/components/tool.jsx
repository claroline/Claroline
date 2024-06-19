import React from 'react'

import {Tool} from '#/main/core/tool'
import {CatalogList} from '#/plugin/cursus/tools/catalog/components/list'

const CatalogTool = (props) =>
  <Tool
    {...props}
    pages={[
      {
        path: '/',
        exact: true,
        component: CatalogList
      }
    ]}
  />

export {
  CatalogTool
}
