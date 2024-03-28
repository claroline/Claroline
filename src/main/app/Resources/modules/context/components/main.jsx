import React, {createElement, useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {makeCancelable} from '#/main/app/api'
import {Routes} from '#/main/app/router'
import {ToolMain} from '#/main/core/tool/containers/main'
import {FooterMain} from '#/main/app/layout/footer/containers/main'
import {trans} from '#/main/app/intl'
import {ContentNotFound} from '#/main/app/content/components/not-found'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentForbidden} from '#/main/app/content/components/forbidden'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {ContextEditor} from '#/main/app/context/editor/containers/main'
import {ContextProfile} from '#/main/app/context/profile/containers/main'
import {getTool} from '#/main/core/tool/utils'
import {Await} from '#/main/app/components/await'

const ContextMain = (props) => {
  // fetch current context data
  useEffect(() => {
    let openQuery
    if (props.name) {
      openQuery = makeCancelable(
        props.open(props.name, props.id)
      )
    }

    return () => {
      if (openQuery) {
        openQuery.cancel()
      }
    }
  }, [props.name, props.id])

  if (!props.loaded) {
    return props.loadingPage ?
      createElement(props.loadingPage) :
      <ContentLoader
        size="lg"
        description={trans('loading')}
      />
  }

  if (props.notFound) {
    return props.notFoundPage ?
      createElement(props.notFoundPage) :
      <ContentNotFound
        size="lg"
        title={trans('not_found')}
        description={trans('not_found_desc')}
      />
  }

  if (!isEmpty(props.accessErrors)) {
    return props.forbiddenPage ?
      createElement(props.forbiddenPage) :
      <ContentForbidden
        size="lg"
        title={trans('access_forbidden')}
        description={trans('access_forbidden_help')}
      />
  }

  if (isEmpty(props.tools)) {
    return (
      <ContentPlaceholder
        size="lg"
        title="Cet espace est vide pour le moment"
      />
    )
  }

  return (
    <>
      {createElement(props.menu)}

      <div className="app-body" role="presentation">
        <div className="app-loader" />

        <Routes
          path={props.path}
          routes={[
            {
              path: '/profile',
              component: ContextProfile
            }, {
              path: '/edit',
              component: ContextEditor,
              onEnter: () => props.openEditor(props.contextData)
            }, {
              path: '/:toolName',
              onEnter: (params = {}) => {
                if (-1 === props.tools.findIndex(tool => tool.name === params.toolName)) {
                  // tool is disabled (or does not exist) for the context
                  // let's go to the default opening of the context
                  props.history.replace(props.path)
                }
              },
              //component: ToolMain,
              render: (routerProps) => {
                const params = routerProps.match.params
                return (
                  <Await
                    for={getTool(params.toolName, props.name)}
                    then={(resolved) => {
                      return createElement(resolved.default.component, {
                        name: params.toolName,
                        path: props.path+'/'+params.toolName,
                      })
                    }}
                   />
                )
              }
            }
          ]}
          redirect={[
            {from: '/', exact: true, to: `/${props.defaultOpening}`, disabled: !props.defaultOpening}
          ]}
        />

        {props.footer && createElement(props.footer)}
      </div>
    </>
  )
}

ContextMain.propTypes = {
  // context info
  path: T.string.isRequired,
  id: T.string,
  name: T.string.isRequired,

  // context status
  loaded: T.bool.isRequired,
  notFound: T.bool.isRequired,
  accessErrors: T.object,
  // context params
  defaultOpening: T.string,
  tools: T.arrayOf(T.shape({

  })),
  // custom context components
  menu: T.elementType,
  footer: T.elementType,
  loadingPage: T.elementType,
  notFoundPage: T.elementType,
  forbiddenPage: T.elementType,

  open: T.func.isRequired,
  history: T.shape({
    replace: T.func.isRequired
  }).isRequired
}

ContextMain.defaultProps = {
  tools: [],
  footer: FooterMain
}

export {
  ContextMain
}
