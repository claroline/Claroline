import React, {createElement, useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {makeCancelable} from '#/main/app/api'
import {Routes} from '#/main/app/router'
import {trans} from '#/main/app/intl'
import {ErrorBoundary} from '#/main/app/components/error-boundary'
import {useLocaleStorage} from '#/main/app/storage'
import {ContentLoader} from '#/main/app/content/components/loader'
import {actions as modalActions} from '#/main/app/overlays/modal'
import {useCtrlKeyPress} from '#/main/app/dom/key'

import {getTool} from '#/main/core/tool/utils'
import {MODAL_COMMAND_PALETTE} from '#/main/app/context/modals/command-palette'
import {ContextSidebar} from '#/main/app/context/components/sidebar'
import {ContextError} from '#/main/app/context/components/error'
import {ContextEditor} from '#/main/app/context/editor/containers/main'
import {ContextProfile} from '#/main/app/context/profile/containers/main'

const ContextMain = (props) => {
  const dispatch = useDispatch()

  const [pinedMenu] = useLocaleStorage('contextMenuPined', false)
  const [toolApps, setToolApps] = useState({loaded: false, tools: []})

  useCtrlKeyPress('k', (event) => {
    dispatch(modalActions.showModal(MODAL_COMMAND_PALETTE))

    event.preventDefault()
    event.stopPropagation()
  })

  // change current context
  useEffect(() => {
    if (props.name) {
      props.open(props.name, props.id)
    }
  }, [props.name, props.id])

  // fetch current context data
  useEffect(() => {
    let openQuery
    if (props.name && !props.loaded) {
      openQuery = makeCancelable(
        props.fetch(props.name, props.id)
      )

      openQuery.promise
        .then((response) => {
          if (props.onOpen) {
            props.onOpen(response.data)
          }
        })
        .then(
          () => openQuery = null,
          () => openQuery = null
        )
    }

    return () => {
      if (openQuery && props.loaded) {
        openQuery.cancel()
      }
    }
  }, [props.loaded])

  // fetch tool apps
  useEffect(() => {
    let appPromise
    if (props.loaded) {
      // load apps for every tool defined in this context
      appPromise = makeCancelable(Promise.all(
        props.tools.map(tool => getTool(tool.name, props.name)
          .then(toolApp => ({
            name: tool.name,
            app: toolApp.default.component
          }))
          .catch(e => console.error(e))
        )
      ))

      appPromise.promise
        .then(loadedApps => {
          setToolApps({
            loaded: true,
            tools: loadedApps.reduce((acc, current) => Object.assign(acc, {
              [current.name]: current.app
            }), {})
          })
        })
        .then(
          () => appPromise = null,
          () => appPromise = null
        )
    } else {
      setToolApps({loaded: false, tools: []})
    }

    return () => {
      if (appPromise) {
        appPromise.cancel()
      }
    }
  }, [props.loaded, props.tools.map(t => t.name).join('-')])

  if (props.loaded && toolApps.loaded) {
    if (!isEmpty(props.error)) {
      return props.errorPage ?
        createElement(props.errorPage, props.error) :
        <ContextError {...props.error} />
    }

    return (
      <ErrorBoundary fallback={<ContextError code="UNKNOWN_ERROR" message="Error while rendering the requested context." />}>
        {pinedMenu &&
          <ContextSidebar />
        }

        <Routes
          path={props.path}
          routes={[
            {
              path: '/profile',
              component: ContextProfile
            }, {
              path: '/edit',
              component: props.editor || ContextEditor
            }, {
              path: '/:toolName',
              onEnter: (params = {}) => {
                const openedTool = props.tools.find(tool => tool.name === params.toolName)
                if (isEmpty(openedTool)) {
                  // the tool is disabled, or does not exist, or the user has no open right on it
                  // redirect to the default opening of the context
                  props.history.replace(props.path)
                }
              },
              render: (routerProps) => {
                const params = routerProps.match.params

                return createElement(toolApps.tools[params.toolName], {
                  name: params.toolName,
                  path: props.path+'/'+params.toolName
                })
              }
            }
          ]}
          redirect={[
            {from: '/', exact: true, to: `/${props.defaultOpening}`, disabled: !props.defaultOpening}
          ]}
        />

        {props.children}
      </ErrorBoundary>
    )
  }

  return props.loadingPage ?
    createElement(props.loadingPage) :
    <ContentLoader
      size="lg"
      description={trans('loading')}
    />
}

ContextMain.propTypes = {
  // context info
  path: T.string.isRequired,
  id: T.string,
  name: T.string.isRequired,

  // context status
  loaded: T.bool.isRequired,
  error: T.object,
  // context params
  defaultOpening: T.string,
  tools: T.arrayOf(T.shape({
    name: T.string.isRequired,
    permissions: T.shape({
      open: T.bool
    })
  })),

  // custom context components
  errorPage: T.elementType,
  loadingPage: T.elementType,
  editor: T.elementType,
  onOpen: T.func,

  fetch: T.func.isRequired,
  open: T.func.isRequired,
  history: T.shape({
    replace: T.func.isRequired
  }).isRequired,
  children: T.node
}

export {
  ContextMain
}
