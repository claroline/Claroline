import React from 'react'
import classes from 'classnames'

import {theme} from '#/main/app/config'
import {implementPropTypes} from '#/main/app/prop-types'

import {OverlayStack} from '#/main/app/overlay/containers/stack'
import {ModalOverlay} from '#/main/app/overlay/modal/containers/overlay'
import {AlertOverlay} from '#/main/app/overlay/alert/containers/overlay'
import {WalkthroughOverlay} from '#/main/app/overlay/walkthrough/containers/overlay'

import {PageSimple as PageSimpleTypes} from '#/main/app/page/prop-types'
import {PageBreadcrumb} from '#/main/app/page/components/breadcrumb'
import {PageWrapper} from '#/main/app/page/components/wrapper'

/**
 * Root of the current page.
 *
 * For now, overlays are managed here.
 * In future version, when the layout will be in React,
 * it'll be moved in higher level.
 */
const PageSimple = props =>
  <PageWrapper
    embedded={props.embedded}
    className={classes(props.className, props.size, {
      fullscreen: props.fullscreen,
      main: !props.embedded,
      embedded: props.embedded
    })}
  >
    <AlertOverlay />

    {!props.embedded &&
      <PageBreadcrumb
        path={props.path}
        className={classes({
          'sr-only': !props.showBreadcrumb || props.fullscreen
        })}
      />
    }

    {props.children}

    <OverlayStack>
      <ModalOverlay />
      <WalkthroughOverlay />
    </OverlayStack>

    {props.styles.map(styleName =>
      <link key={styleName} rel="stylesheet" type="text/css" href={theme(styleName)} />
    )}
  </PageWrapper>

implementPropTypes(PageSimple, PageSimpleTypes)

export {
  PageSimple
}
