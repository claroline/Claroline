import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {makeCancelable} from '#/main/app/api'
import {useReducer} from '#/main/app/store/reducer'
import {Routes, RouteTypes, RedirectTypes} from '#/main/app/router'

import {ToolEditor} from '#/main/core/tool/editor/containers/main'
import {actions, reducer, selectors} from '#/main/core/tool/store'
import {PageContext} from '#/main/app/page/context'

const ToolMain = (props) => {
  useReducer(selectors.STORE_NAME, reducer)

  const toolPath = useSelector(selectors.path)
  const contextType = useSelector(selectors.contextType)
  const contextId = useSelector(selectors.contextId)
  const canEdit = useSelector((state) => selectors.hasPermission('edit', state))
  const canFollow = useSelector((state) => selectors.hasPermission('follow', state))

  const dispatch = useDispatch()

  // fetch current tool data
  useEffect(() => {
    let openQuery
    if (props.name) {
      openQuery = makeCancelable(
        dispatch(actions.open(props.name, contextType, contextId))
      )
    }

    return () => {
      if (openQuery) {
        openQuery.cancel()
      }
    }
  }, [props.name, contextType, contextId])

  return (
    <PageContext.Provider
      value={{
        menu: (props.menu || []).concat([
          {
            name: 'dashboard',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-gauge',
            label: trans('dashboard'),
            tooltip: 'bottom',
            target: toolPath+'/dashboard',
            displayed: !!props.dashboard && canFollow
          }, {
            name: 'parameters',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-sliders',
            label: trans('parameters'),
            tooltip: 'bottom',
            target: toolPath+'/edit',
            displayed: canEdit && 'administration' !== contextType
          }
        ]),
        actions: props.actions,
        styles: props.styles
      }}
    >
      <Routes
        path={toolPath}
        routes={[
          {
            path: '/edit',
            disabled: !canEdit,
            component: props.editor || ToolEditor
          }, {
            path: '/dashboard',
            disabled: !props.dashboard || !canFollow,
            component: props.dashboard
          }
        ]
          .concat(props.pages || [])
          .concat(!isEmpty(props.children) ? [
            {
              path: '/',
              render: () => props.children
            }
          ] : [])
        }
        redirect={props.redirect}
      />
    </PageContext.Provider>
  )
}

ToolMain.propTypes = {
  /**
   * The name of the tool.
   */
  name: T.string.isRequired,

  /**
   * A list of additional styles required by the tool.
   */
  styles: T.arrayOf(T.string),

  menu: T.arrayOf(T.shape({

  })),

  actions: T.arrayOf(T.shape({

  })),

  redirect: T.arrayOf(T.shape(
    RedirectTypes.propTypes
  )),

  /**
   * Common pages.
   * Each common page MUST start with the corresponding component :
   *  - editor => ToolEditor
   *  - dashboard => ToolDashboard
   */
  editor: T.any,
  dashboard: T.any,

  /**
   * A list of sections/pages of the tool.
   * If your tool contains only one section/page, use `children`.
   *
   * NB. Each page MUST start with a `ToolPage` component.
   */
  pages: T.arrayOf(T.shape(
    RouteTypes.propTypes
  )),

  /**
   * The tool content if there is only one section/page in the tool.
   */
  children: T.node
}

export {
  ToolMain
}
