import React, {createElement, useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {makeCancelable} from '#/main/app/api'
import {getResource} from '#/main/core/resource/utils'

import {ResourceSkeleton} from '#/main/core/resource/components/skeleton'
import {ResourceError} from '#/main/core/resource/components/error'
import {ErrorBoundary} from '#/main/app/components/error-boundary'

const ResourceWrapper = (props) => {
  const [app, setApp] = useState(null)

  // change current resource
  useEffect(() => {
    if (props.slug) {
      props.open(props.slug, props.embedded)
    }
  }, [props.slug])

  // fetch resource data
  useEffect(() => {
    let openQuery
    if (props.slug && !props.loaded) {
      openQuery = makeCancelable(
        props.fetch(props.slug, props.embedded)
      )

      openQuery.promise
        .then(response => {
          return getResource(get(response, 'resourceNode.meta.type'))
            .then((resourceApp) => {
              setApp({
                type: get(response, 'resourceNode.meta.type'),
                component: resourceApp.default.component,
                data: response
              })
            })
            .catch(e => console.error(e))
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

  if (props.loaded && !isEmpty(props.error)) {
    return (
      <ResourceError {...props.error} />
    )
  }

  if (!props.loaded || !app) {
    return (
      <ResourceSkeleton />
    )
  }

  return (
    <ErrorBoundary fallback={<ResourceError code="UNKNOWN_ERROR" message="Error while rendering the requested resource." />}>
      {createElement(app.component, {
        path: props.path,
        type: app.type,
        slug: props.slug,
        open: (resourceType, resourceSlug) => props.openType(resourceType, resourceSlug, app.data)
      })}
    </ErrorBoundary>
  )
}

ResourceWrapper.propTypes = {
  path: T.string.isRequired,
  slug: T.string.isRequired,
  embedded: T.bool.isRequired,

  // from store
  loaded: T.bool.isRequired,
  error: T.object,
  open: T.func.isRequired,
  fetch: T.func.isRequired,
  openType: T.func.isRequired
}

ResourceWrapper.defaultProps = {
  embedded: false
}

export {
  ResourceWrapper
}
