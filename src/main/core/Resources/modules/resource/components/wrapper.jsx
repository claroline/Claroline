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

  useEffect(() => {
    let openQuery
    if (props.slug) {
      openQuery = makeCancelable(
        props.open(props.slug, props.embedded)
          .then(response => getResource(get(response, 'resourceNode.meta.type'))
            .then((resourceApp) => {
              setApp({
                type: get(response, 'resourceNode.meta.type'),
                component: resourceApp.default.component,
                data: response
              })
            })
          )
      )
    }

    return () => {
      if (openQuery) {
        openQuery.cancel()
      }
    }
  }, [props.slug])

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
    path: props.path + '/' + props.slug,
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
  openType: T.func.isRequired
}

ResourceWrapper.defaultProps = {
  embedded: false
}

export {
  ResourceWrapper
}
