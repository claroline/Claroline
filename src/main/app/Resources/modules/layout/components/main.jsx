import React, {useState, useEffect, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {makeCancelable} from '#/main/app/api'
import {Routes, Redirect} from '#/main/app/router'
import {getContexts} from '#/main/app/context/registry'

import {HeaderMain} from '#/main/app/layout/header/containers/main'

import {LayoutForbidden} from '#/main/app/layout/containers/forbidden'
import {HomeRegistration} from '#/main/app/layout/components/registration'
import {SendPassword} from '#/main/app/layout/components/send-password'
import {NewPassword} from '#/main/app/layout/components/new-password'
import {HomeLogin} from '#/main/app/layout/components/login'

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
            {from: '/', exact: true, to: '/unavailable', disabled: !props.unavailable},

            // disable registration and redirect user if no self registration or the user is already authenticated
            {from: '/registration', to: '/', disabled: props.selfRegistration || !props.authenticated},

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
            {
              path: '/unavailable',
              disabled: !props.unavailable,
              component: LayoutForbidden
            }, {
              path: '/reset_password',
              disabled: props.authenticated || !props.changePassword,
              component: SendPassword
            }, {
              path: '/newpassword/:hash',
              component: NewPassword
            }, {
              path: '/login/:forceInternalAccount(account)?',
              disabled: props.authenticated,
              component: HomeLogin
            }, {
              path: '/registration',
              disabled: props.unavailable || !props.selfRegistration || props.authenticated,
              component: HomeRegistration
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
  changePassword: T.bool.isRequired,
  selfRegistration: T.bool,
  toggleMenu: T.func.isRequired
}

export {
  LayoutMain
}
