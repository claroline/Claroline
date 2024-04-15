import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {makeCancelable} from '#/main/app/api'
import {hasPermission} from '#/main/app/security'
import {useReducer} from '#/main/app/store/reducer'
import {Routes, RouteTypes, RedirectTypes} from '#/main/app/router'

import {ToolContext} from '#/main/core/tool/context'
import {ToolEditor} from '#/main/core/tool/editor/containers/main'
import {actions, reducer, selectors} from '#/main/core/tool/store'

const ToolMain = (props) => {
  useReducer(selectors.STORE_NAME, reducer)

  const toolPath = useSelector(selectors.path)
  const contextType = useSelector(selectors.contextType)
  const contextId = useSelector(selectors.contextId)
  const canEdit = useSelector((state) => hasPermission('edit', selectors.toolData(state)))

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
    <ToolContext.Provider
      value={{
        menu: props.menu,
        actions: props.actions,
        styles: props.styles
      }}
    >
      {(!isEmpty(props.pages) || props.children) &&
        <Routes
          path={toolPath}
          routes={[
            {
              path: '/edit',
              disabled: !canEdit,
              component: ToolEditor
            }
          ]
            .concat(props.pages || [])
            .concat([
              {
                path: '/',
                disabled: isEmpty(props.children),
                render: () => props.children
              }
            ])
          }
          redirect={props.redirect}
        />
      }
    </ToolContext.Provider>
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

  /**
   * A list of sections/pages of the tool.
   * If your tool contains only one section/page, use `children`.
   *
   * NB. Each page MUST start with a `ToolPage` component.
   */
  pages: T.arrayOf(T.shape(
    RouteTypes.propTypes
  )),
  redirect: T.arrayOf(T.shape(
    RedirectTypes.propTypes
  )),

  /**
   * The tool content if there is only one section/page in the tool.
   */
  children: T.node
}

ToolMain.defaultProps = {
  styles: []
}

export {
  ToolMain
}
