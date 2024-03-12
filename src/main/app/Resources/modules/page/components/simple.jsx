import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import {Helmet} from 'react-helmet'

import {asset} from '#/main/app/config/asset'

import {PageSimple as PageSimpleTypes} from '#/main/app/page/prop-types'

const PageWrapper = props => createElement(!props.embedded ? 'main':'article', {
  id: props.id,
  className: classes('page', props.className)
}, props.children)

PageWrapper.propTypes = {
  id: T.string,
  className: T.string,
  embedded: T.bool.isRequired,
  children: T.node
}

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

    {!isEmpty(props.styles) &&
      <Helmet>
        {props.styles.map(style =>
          <link key={style} rel="stylesheet" type="text/css" href={theme(style)} />
        )}
      </Helmet>
    }

    {props.children}
  </PageWrapper>

PageSimple.propTypes = PageSimpleTypes.propTypes
PageSimple.defaultProps = PageSimpleTypes.defaultProps

export {
  PageSimple
}
