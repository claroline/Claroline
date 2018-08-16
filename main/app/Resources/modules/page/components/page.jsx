import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {theme} from '#/main/app/config'

import {Router} from '#/main/app/router'
import {ModalOverlay} from '#/main/app/overlay/modal/containers/overlay'
import {AlertOverlay} from '#/main/app/overlay/alert/containers/overlay'

import {Page as PageTypes} from '#/main/app/page/prop-types'
import {PageHeader} from '#/main/app/page/components/header'

const PageWrapper = props =>
  <Router embedded={props.embedded}>
    {React.createElement(!props.embedded ? 'main':'section', {
      className: classes('page', props.className)
    }, props.children)}
  </Router>

PageWrapper.propTypes = {
  className: T.string,
  embedded: T.bool.isRequired,
  children: T.node
}

/**
 * Root of the current page.
 *
 * For now, modals are managed here.
 * In future version, when the layout will be in React,
 * it'll be moved in higher level.
 */
const Page = props =>
  <PageWrapper
    styles={props.styles}
    embedded={props.embedded}
    className={classes(props.className, props.size, {
      fullscreen: props.fullscreen,
      main: !props.embedded,
      embedded: props.embedded
    })}
  >
    <AlertOverlay />

    <PageHeader
      title={props.title}
      subtitle={props.subtitle}
      icon={props.icon}
      poster={props.poster}
      toolbar={props.toolbar}
      actions={props.actions}
    />

    <div className="page-content" role="presentation">
      {props.children}
    </div>

    <ModalOverlay />

    {props.styles.map(styleName =>
      <link key={styleName} rel="stylesheet" type="text/css" href={theme(styleName)} />
    )}
  </PageWrapper>


implementPropTypes(Page, PageTypes, {
  children: T.node.isRequired
})

export {
  Page
}
