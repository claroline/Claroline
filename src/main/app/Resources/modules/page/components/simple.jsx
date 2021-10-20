import React from 'react'
import classes from 'classnames'
import {Helmet} from 'react-helmet'

import {implementPropTypes} from '#/main/app/prop-types'
import {asset} from '#/main/app/config/asset'

import {PageSimple as PageSimpleTypes} from '#/main/app/page/prop-types'
import {PageBreadcrumb} from '#/main/app/page/components/breadcrumb'
import {PageWrapper} from '#/main/app/page/components/wrapper'

/**
 * Root of the current page.
 */
const PageSimple = props =>
  <PageWrapper
    id={props.id}
    embedded={props.embedded}
    className={classes(props.className, props.size, {
      fullscreen: props.fullscreen,
      main: !props.embedded,
      embedded: props.embedded
    })}
  >
    {!props.embedded && props.meta &&
      <Helmet>
        {props.meta.title &&
          <title>{props.meta.title}</title>
        }

        {props.meta.title &&
          <meta property="og:title" content={props.meta.title}/>
        }

        <meta property="og:type" content={props.meta.type || 'website'} />

        {props.meta.poster &&
          <meta property="og:image" content={asset(props.meta.poster)}/>
        }

        {props.meta.description &&
          <meta name="description" property="og:description" content={props.meta.description} />
        }
      </Helmet>
    }

    {!props.embedded &&
      <PageBreadcrumb
        path={props.path}
        className={classes({
          'sr-only': !props.showBreadcrumb || props.fullscreen
        })}
      />
    }

    {props.children}
  </PageWrapper>

implementPropTypes(PageSimple, PageSimpleTypes)

export {
  PageSimple
}
