import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {Action, PromisedAction} from '#/main/app/action/prop-types'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'
import {PageContext} from '#/main/app/page/context'

import {selectors} from '#/main/core/resource/store'
import {ResourceEditor} from '#/main/core/resource/editor/containers/main'
import {ResourceDashboard} from '#/main/core/resource/dashboard'

const Resource = props => {
  const [loaded, setLoaded] = useState(false)

  const embedded = useSelector(selectors.embedded)
  const resourcePath = useSelector(selectors.path)
  const canEdit = useSelector(selectors.canEdit)
  const canFollow = useSelector(selectors.canFollow)

  useEffect(() => {
    props.open(props.type, props.slug)
    setLoaded(true)
  }, [props.slug])

  return (
    <PageContext.Provider
      value={{
        embedded: embedded,
        menu: [
          {
            name: 'overview',
            type: LINK_BUTTON,
            label: trans('resource_overview', {}, 'resource'),
            target: resourcePath,
            displayed: !!props.overviewPage,
            exact: true
          }
        ].concat(props.menu || []),
        actions: props.actions,
        styles: props.styles
      }}
    >
      {loaded &&
        <Routes
          path={resourcePath}
          routes={[
            {
              path: '/edit',
              component: props.editor || ResourceEditor,
              disabled: !canEdit
            }, {
              path: '/dashboard',
              component: props.dashboard || ResourceDashboard,
              disabled: !canFollow
            }
          ]
            .concat(props.pages || [])
            .concat([
              {
                path: '/',
                disabled: !props.overviewPage,
                component: props.overviewPage,
                exact: true
              }
            ])
          }
          redirect={props.redirect}
        />
      }

      {loaded && props.children}
    </PageContext.Provider>
  )
}

Resource.propTypes = {
  /**
   * The type of the tool.
   */
  type: T.string.isRequired,
  slug: T.string.isRequired,
  styles: T.arrayOf(T.string),
  children: T.node,
  open: T.func.isRequired,
  actions: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      Action.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedAction.propTypes
    )
  ]),
  menu: T.array,
  redirect: T.array,
  pages: T.array,

  /**
   * The resource overview component
   * NB. This SHOULD extend the base <ResourceOverview /> component.
   */
  overviewPage: T.elementType,
  /**
   * The resource editor component
   * NB. This SHOULD extend the base <ResourceEditor /> component.
   */
  editor: T.elementType,
  /**
   * The resource editor component
   * NB. This SHOULD extend the base <ResourceDashboard /> component.
   */
  dashboard: T.elementType
}

export {
  Resource
}
