import React, {createElement, useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {makeCancelable} from '#/main/app/api'
import {getResource} from '#/main/core/resources'

import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentNotFound} from '#/main/app/content/components/not-found'

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

  if (!props.loaded || !app) {
    return (
      <ContentLoader
        size="lg"
        description={trans('loading', {}, 'resource')}
      />
    )
  }

  if (props.notFound) {
    return (
      <ContentNotFound
        size="lg"
        title={trans('not_found', {}, 'resource')}
        description={trans('not_found_desc', {}, 'resource')}
      />
    )
  }

  return createElement(app.component, {
    path: props.path,
    type: app.type,
    slug: props.slug,
    open: (resourceType, resourceSlug) => props.openType(resourceType, resourceSlug, app.data)
  })
}

ResourceWrapper.propTypes = {
  path: T.string.isRequired,
  slug: T.string.isRequired,
  embedded: T.bool.isRequired,

  // from store
  loaded: T.bool.isRequired,
  notFound: T.bool.isRequired,
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
