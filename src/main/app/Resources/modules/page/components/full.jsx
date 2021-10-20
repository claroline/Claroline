import React, {Component} from 'react'
import classes from 'classnames'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {PageFull as PageFullTypes} from '#/main/app/page/prop-types'
import {PageSimple} from '#/main/app/page/components/simple'
import {PageHeader} from '#/main/app/page/components/header'
import {PageContent} from '#/main/app/page/components/content'

/**
 * Root of the current page.
 */
class PageFull extends Component {
  constructor(props) {
    super(props)

    this.state = {
      fullscreen: this.props.fullscreen
    }

    this.toggleFullscreen = this.toggleFullscreen.bind(this)
  }

  componentDidUpdate(prevProps) {
    if (this.props.fullscreen !== prevProps.fullscreen) {
      this.setState({fullscreen: this.props.fullscreen})
    }
  }

  toggleFullscreen() {
    this.setState({fullscreen: !this.state.fullscreen})
  }

  render() {
    const baseActions = [
      {
        name: 'fullscreen',
        type: CALLBACK_BUTTON,
        icon: classes('fa fa-fw', {
          'fa-expand': !this.state.fullscreen,
          'fa-compress': this.state.fullscreen
        }),
        label: trans(this.state.fullscreen ? 'fullscreen_off' : 'fullscreen_on'),
        callback: this.toggleFullscreen
      }
    ]

    let actions
    if (this.props.actions instanceof Promise) {
      actions = this.props.actions.then(promisedActions => promisedActions.concat(baseActions))
    } else {
      actions = (this.props.actions || []).concat(baseActions)
    }

    return (
      <PageSimple
        {...omit(this.props, 'showHeader', 'showTitle', 'header', 'title', 'subtitle', 'icon', 'poster', 'toolbar', 'actions', 'fullscreen')}
        fullscreen={this.state.fullscreen}
        meta={merge({}, {
          title: this.props.title,
          poster: this.props.poster
        }, this.props.meta || {})}
      >
        {this.props.showHeader &&
          <PageHeader
            id={this.props.id}
            showTitle={this.props.showTitle}
            title={this.props.title}
            subtitle={this.props.subtitle}
            icon={this.props.icon}
            poster={this.props.poster}
            toolbar={this.props.toolbar}
            disabled={this.props.disabled}
            actions={actions}
          >
            {this.props.header}
          </PageHeader>
        }

        <PageContent className={classes({'main-page-content': !this.props.embedded})}>
          {this.props.children}
        </PageContent>
      </PageSimple>
    )
  }
}

implementPropTypes(PageFull, PageFullTypes)

export {
  PageFull
}
