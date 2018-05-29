import React, {Component} from 'react'
import classes from 'classnames'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/core/translation'
import {Page as PageTypes} from '#/main/app/page/prop-types'

import {Router} from '#/main/app/router'
import {ModalOverlay} from '#/main/app/overlay/modal/containers/overlay'
import {AlertOverlay} from '#/main/app/overlay/alert/containers/overlay'

import {PageHeader} from '#/main/app/page/components/header'

const PageWrapper = props =>
  <Router embedded={props.embedded}>
    {!props.embedded ?
      <main className={classes('page', props.className)}>
        {props.children}
      </main> :
      <section className={classes('page', props.className)}>
        {props.children}
      </section>
    }
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
 *
 * @todo maybe manage fullscreen in redux store
 * It will cause issue when you pass from a fullscreen page to a one that does not support it.
 * (there will be no button to go back in normal mode, maybe add the fullscreen on all page or reset it)
 */
class Page extends Component {
  constructor(props) {
    super(props)

    this.state = {
      fullscreen: !this.props.embedded && this.props.fullscreen
    }

    this.toggleFullscreen = this.toggleFullscreen.bind(this)
  }

  toggleFullscreen() {
    this.setState({fullscreen: !this.state.fullscreen})
  }

  render() {
    return (
      <PageWrapper
        embedded={this.props.embedded}
        className={classes(this.props.className, {
          fullscreen: this.state.fullscreen,
          main: !this.props.embedded,
          embedded: this.props.embedded
        })}
      >
        <AlertOverlay />

        <PageHeader
          {...omit(this.props, 'className', 'embedded', 'fullscreen', 'children')}
          actions={this.props.actions
            // add the fullscreen actions (it must be added to the Page toolbar to be added)
            .concat([
              {
                name: 'fullscreen',
                type: 'callback',
                icon: classes('fa fa-fw', {
                  'fa-expand': !this.state.fullscreen,
                  'fa-compress': this.state.fullscreen
                }),
                label: trans(this.state.fullscreen ? 'fullscreen_off' : 'fullscreen_on'),
                callback: this.toggleFullscreen
              }
            ])
            // only get displayed actions
            .filter(action => undefined === action.displayed || action.displayed)
          }
        />

        {this.props.children}

        <ModalOverlay />
      </PageWrapper>
    )
  }
}


implementPropTypes(Page, PageTypes, {
  children: T.node.isRequired
})

export {
  Page
}
