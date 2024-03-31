import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {makeCancelable} from '#/main/app/api'
import {Routes, RouteTypes, RedirectTypes} from '#/main/app/router'

import {ToolContext} from '#/main/core/tool/context'

const ToolMain = (props) => {
  // fetch current context data
  useEffect(() => {
    let openQuery
    if (props.name) {
      openQuery = makeCancelable(
        props.open(props.name, props.contextType, props.contextId)
      )
    }

    return () => {
      if (openQuery) {
        openQuery.cancel()
      }
    }
  }, [props.name, props.contextType, props.contextId])

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
          path={props.path}
          routes={[]
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
  name: T.string.isRequired,
  styles: T.arrayOf(T.string),
  pages: T.arrayOf(T.shape(
    RouteTypes.propTypes
  )),
  redirect: T.arrayOf(T.shape(
    RedirectTypes.propTypes
  )),
  children: T.node,

  // from store
  path: T.string.isRequired,
  contextType: T.string.isRequired,
  contextId: T.string,
  open: T.func.isRequired
}

ToolMain.defaultProps = {
  styles: []
}

export {
  ToolMain
}
