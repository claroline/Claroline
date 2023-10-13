import React, {useState, useEffect, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {makeCancelable} from '#/main/app/api'
import {Routes, Redirect} from '#/main/app/router'

import {HeaderMain} from '#/main/app/layout/header/containers/main'

import {HomeMain} from '#/main/app/layout/sections/home/containers/main'

import {getContexts} from '#/main/app/context/registry'

const LayoutMain = props => {
  const [appContexts, setAppContexts] = useState([])

  useEffect(() => {
    const contextFetching = makeCancelable(getContexts())

    contextFetching.promise.then(definedContexts => setAppContexts(definedContexts))

    return () => contextFetching.cancel()
  })

  return (
    <>
      <HeaderMain
        unavailable={props.unavailable}
        toggleMenu={props.toggleMenu}
      />

      {!isEmpty(appContexts) &&
        <Routes
          redirect={[
            /*{from: '/desktop', to: '/', disabled: !props.unavailable},
            {from: '/admin',   to: '/', disabled: !props.unavailable},*/
            // for retro-compatibility. DO NOT REMOVE !
            {from: '/home', to: '/public'}
          ]}
          routes={[
            // for retro-compatibility. DO NOT REMOVE !
            // NB. I don't use the standard `redirect` prop, because we can not catch params.
            // We use location pathname to keep params not handled by this route (ex. tool path)
            {
              path: '/desktop/workspaces/open/:slug',
              render: (routerProps) => (
                <Redirect to={routerProps.location.pathname.replace(
                  `/desktop/workspaces/open/${routerProps.match.params.slug}`,
                  `/workspace/${routerProps.match.params.slug}`
                )} />
              )
            }
          ].concat(appContexts.map(appContext => ({
            path: appContext.path,
            render: (routerProps) => {
              const params = routerProps.match.params

              return createElement(appContext.component, {
                name: appContext.name,
                id: params.contextId
              })
            }
          })), [
            // it must be declared last otherwise it will always match.
            // and it cannot be set to exact: true because it contains sub routes for maintenance, login and registration.
            {
              path: '/',
              component: HomeMain
            }
          ])}
        />
      }
    </>
  )
}

LayoutMain.propTypes = {
  unavailable: T.bool.isRequired,
  authenticated: T.bool.isRequired,
  toggleMenu: T.func.isRequired
}

export {
  LayoutMain
}
