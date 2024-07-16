import React from 'react'
import {connect} from 'react-redux'

import {Tool} from '#/main/core/tool'
import {route} from '#/plugin/cursus/routing'
import {hasPermission} from '#/main/app/security'
import {withReducer} from '#/main/app/store/reducer'
import {Course} from '#/plugin/cursus/course/containers/main'
import {CatalogList} from '#/plugin/cursus/tools/catalog/components/list'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {reducer, selectors} from '#/plugin/cursus/tools/catalog/store'
import {actions as courseActions, reducer as courseReducer, selectors as courseSelectors} from '#/plugin/cursus/course/store'

const CatalogToolComponent = (props) =>
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

const CatalogTool = withReducer(courseSelectors.STORE_NAME, courseReducer)(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        path: toolSelectors.path(state),
        course: courseSelectors.course(state),
        canEdit: hasPermission('edit', toolSelectors.toolData(state))
      }),
      (dispatch) => ({
        open(slug) {
          dispatch(courseActions.open(slug))
        }
      })
    )(CatalogToolComponent)
  )
)

export {
  CatalogTool
}
