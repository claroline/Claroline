import React, {createElement, useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'
import {hasPermission} from '#/main/app/security'

import {ResourceContext} from '#/main/core/resource/context'
import {selectors} from '#/main/core/resource/store'
import {ResourceRestrictions} from '#/main/core/resource/containers/restrictions'
import {ResourceEditor} from '#/main/core/resource/editor/containers/main'
import {ResourceEvaluations} from '#/main/evaluation/resource/evaluation/containers/main'
import {LogsMain} from '#/main/log/resource/logs/containers/main'
import {ResourceOverview} from '#/main/core/resource/components/overview'


const ResourceMain = props => {
  const [loaded, setLoaded] = useState(false)

  const resourcePath = useSelector(selectors.path)
  const accessErrors = useSelector(selectors.accessErrors)
  const canEdit = useSelector((state) => hasPermission('edit', selectors.resourceNode(state)))

  useEffect(() => {
    props.open(props.type, props.slug)
    setLoaded(true)
  }, [props.slug])

  return (
    <ResourceContext.Provider
      value={{
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
        disabledActions: props.disabledActions,
        styles: props.styles
      }}
    >
      {loaded && !isEmpty(accessErrors) &&
        <ResourceRestrictions />
      }

      {loaded && isEmpty(accessErrors) && (!isEmpty(props.pages) || props.children) &&
        <Routes
          path={resourcePath}
          routes={[
            {
              path: '/edit',
              disabled: !canEdit,
              component: props.editor
            }, {
              path: '/evaluation',
              component: ResourceEvaluations
            }, {
              path: '/logs',
              component: LogsMain
            }
          ]
            .concat(props.pages || [])
            .concat([
              {
                path: '/',
                disabled: !props.overviewPage,
                component: props.overviewPage,
                exact: true
              }, {
                path: '/',
                disabled: isEmpty(props.children),
                render: () => props.children
              }
            ])
          }
          redirect={props.redirect}
        />
      }
    </ResourceContext.Provider>
  )
}

ResourceMain.propTypes = {
  /**
   * The type of the tool.
   */
  type: T.string.isRequired,
  slug: T.string.isRequired,
  styles: T.arrayOf(T.string),
  children: T.node,
  open: T.func.isRequired,
  /**
   * The resource overview component
   * NB. This SHOULD extend the base <ResourceOverview /> component.
   */
  overviewPage: T.elementType,
  /**
   * The resource editor component
   * NB. This SHOULD extend the base <ResourceEditor /> component.
   */
  editor: T.elementType
}

ResourceMain.defaultProps = {
  styles: [],
  actions: [],
  overviewPage: ResourceOverview,
  editor: ResourceEditor
}

export {
  ResourceMain
}
