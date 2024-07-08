import React from 'react'
import {connect} from 'react-redux'

import {Tool} from '#/main/core/tool'
import {withReducer} from '#/main/app/store/reducer'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {reducer, selectors} from '#/plugin/cursus/tools/catalog/store'
import {CatalogList} from '#/plugin/cursus/tools/catalog/components/list'

const CatalogToolComponent = (props) =>
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

const CatalogTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    })
  )(CatalogToolComponent)
)

export {
  CatalogTool
}
