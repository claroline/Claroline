import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import {Helmet} from 'react-helmet'

import {theme} from '#/main/theme/config'
import {trans} from '#/main/app/intl/translation'
import {makeCancelable} from '#/main/app/api'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentForbidden} from '#/main/app/content/components/forbidden'
import {ContentNotFound} from '#/main/app/content/components/not-found'

const ToolMain = (props) => {
  // fetch current context data
  useEffect(() => {
    let openQuery
    if (props.name) {
      openQuery = makeCancelable(
        props.open(props.name, props.contextType, props.contextId)
      )
    }

    return () => {
      if (openQuery) {
        openQuery.cancel()
      }
    }
  }, [props.name, props.contextType, props.contextId])

  if (!props.loaded) {
    return (
      <ContentLoader
        size="lg"
        description={trans('loading', {}, 'tools')}
      />
    )
  }

  if (props.notFound) {
    return (
      <ContentNotFound
        size="lg"
        title={trans('not_found', {}, 'tools')}
        description={trans('not_found_desc', {}, 'tools')}
      />
    )
  }

  if (props.accessDenied) {
    return (
      <ContentForbidden
        size="lg"
        title={trans('forbidden', {}, 'tools')}
        description={trans('forbidden_desc', {}, 'tools')}
      />
    )
  }

  return (
    <>
      {props.loaded && props.children}

      {0 !== props.styles.length &&
        <Helmet>
          {props.styles.map(style =>
            <link key={style} rel="stylesheet" type="text/css" href={theme(style)} />
          )}
        </Helmet>
      }
    </>
  )
}

ToolMain.propTypes = {
  name: T.string.isRequired,
  styles: T.arrayOf(T.string),
  children: T.node,

  // from store
  contextType: T.string.isRequired,
  contextId: T.string,
  loaded: T.bool.isRequired,
  notFound: T.bool.isRequired,
  accessDenied: T.bool.isRequired,
  open: T.func.isRequired
}

ToolMain.defaultProps = {
  styles: []
}

export {
  ToolMain
}
