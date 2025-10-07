import React, {useState, useEffect, createElement} from 'react'
import {useDispatch, useSelector} from 'react-redux'
import {useHistory} from 'react-router-dom'
import get from 'lodash/get'

import {API_REQUEST, makeCancelable} from '#/main/app/api'
import {Routes, Redirect} from '#/main/app/router'
import {getContexts} from '#/main/app/context/registry'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as configSelectors} from '#/main/app/config/store'

import {PlatformForbidden} from '#/main/app/platform/components/forbidden'
import {PlatformRegistration} from '#/main/app/platform/components/registration'
import {PlatformSendPassword} from '#/main/app/platform/components/send-password'
import {PlatformNewPassword} from '#/main/app/platform/components/new-password'
import {PlatformLogin} from '#/main/app/platform/components/login'
import {PlatformMenu} from '#/main/app/platform/menu'
import {selectors} from '#/main/app/platform/store'
import {UserEditor} from '#/main/community/user/editor'

const ResourceRedirect = (props) => {
  const dispatch = useDispatch()
  const [resourceNode, setResourceNode] = useState(null)

  useEffect(() => {
    if (props.match.params.slug) {
      dispatch({[API_REQUEST]: {
        url: ['apiv2_resource_get', {field: 'slug', id: props.match.params.slug}],
        success: (response) => setResourceNode(response)
      }})
    }
  }, [get(props, 'match.params.slug')])

  if (resourceNode) {
    return <Redirect to={`/workspace/${get(resourceNode, 'workspace.slug')}/resources/${resourceNode.slug}`} />
  }

  return null
}

const Platform = () => {
  const history = useHistory()

  const availableContexts = useSelector(selectors.availableContexts)
  const unavailable = useSelector(selectors.unavailable)
  const authenticated = useSelector(securitySelectors.isAuthenticated)
  const currentUser = useSelector(securitySelectors.currentUser)
  const selfRegistration = useSelector(selectors.selfRegistration)
  const changePassword = useSelector((state) => configSelectors.param(state, 'authentication.login.changePassword'))

  const [loaded, setLoaded] = useState(false)
  const [appContexts, setAppContexts] = useState([])

  useEffect(() => {
    let contextFetching
    if (!loaded) {
      contextFetching = makeCancelable(getContexts())

      contextFetching.promise
        .then(definedContexts => {
          setAppContexts(definedContexts)
          setLoaded(true)
        })
        .then(
          () => contextFetching = null,
          () => contextFetching = null
        )
    }

    return () => {
      if (contextFetching) {
        contextFetching.cancel()
      }
    }
  }, [loaded])

  return (
    <Routes
      redirect={[
        {from: '/', exact: true, to: '/unavailable', disabled: !unavailable},

        // disable registration and redirect user if no self-registration or the user is already authenticated
        {from: '/registration', to: '/', disabled: selfRegistration || !authenticated},
        {from: '/login', exact: true, to: '/', disabled: !authenticated},

        {from: '/', exact: true, to: '/login', disabled: -1 !== availableContexts.findIndex(c => 'public' === c.name) || authenticated},
        {from: '/', exact: true, to: '/public', disabled: -1 === availableContexts.findIndex(c => 'public' === c.name) || authenticated},
        {from: '/', exact: true, to: '/desktop', disabled: !authenticated},

        // For retro-compatibility. DO NOT REMOVE!
        {from: '/home', to: '/public'}
      ]}
      routes={[
        // For retro-compatibility. DO NOT REMOVE!
        // NB. I don't use the standard `redirect` prop, because we cannot catch params.
        // We use location pathname to keep params not handled by this route (ex. tool path)
        {
          path: '/desktop/workspaces/open/:slug',
          render: (routerProps) => (
            <Redirect to={routerProps.location.pathname.replace(
              `/desktop/workspaces/open/${routerProps.match.params.slug}`,
              `/workspace/${routerProps.match.params.slug}`
            )} />
          )
        },
        // For retro-compatibility <15.0. DO NOT REMOVE!
        {
          path: '/desktop/resources/:slug',
          component: ResourceRedirect
        }
      ].concat(appContexts.map(appContext => ({
        path: appContext.path,
        onEnter: () => {
          if (-1 === availableContexts.findIndex(availableContext => appContext.name === availableContext.name)) {
            // context is not enabled
            history.replace('/')
          }
        },
        render: (routerProps) => {
          const params = routerProps.match.params

          return (
            <>
              {authenticated &&
                <PlatformMenu />
              }

              {createElement(appContext.component, {
                name: appContext.name,
                id: params.contextId
              })}
            </>
          )
        }
      })), [
        {
          path: '/account',
          disabled: !authenticated,
          render: () => (
            <UserEditor
              username={currentUser.username}
              path="/account"
            />
          )
        }, {
          path: '/unavailable',
          disabled: !unavailable,
          component: PlatformForbidden
        }, {
          path: '/reset_password',
          disabled: authenticated || !changePassword,
          component: PlatformSendPassword
        }, {
          path: '/newpassword/:hash',
          component: PlatformNewPassword
        }, {
          path: '/login/:forceInternalAccount(account)?',
          disabled: authenticated,
          component: PlatformLogin
        }, {
          path: '/registration',
          disabled: unavailable || !selfRegistration || authenticated,
          component: PlatformRegistration
        }
      ])}
    />
  )
}

export {
  Platform
}
