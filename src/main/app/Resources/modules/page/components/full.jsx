import React, {Component} from 'react'
import classes from 'classnames'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

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
      fullscreen: this.props.fullscreen,
      actions: []
    }

    this.toggleFullscreen = this.toggleFullscreen.bind(this)
  }

  componentDidMount() {
    this.generateActions()
  }

  componentDidUpdate(prevProps) {
    if (this.props.fullscreen !== prevProps.fullscreen) {
      this.setState({fullscreen: this.props.fullscreen})
    }

    if (this.props.actions !== prevProps.actions) {
      this.generateActions()
    }
  }

  generateActions() {
    const fullscreenAction = {
      name: 'fullscreen',
      type: CALLBACK_BUTTON,
      icon: classes('fa fa-fw', {
        'fa-expand': !this.state.fullscreen,
        'fa-times': this.state.fullscreen
      }),
      label: trans(this.state.fullscreen ? 'fullscreen_off' : 'fullscreen_on'),
      callback: this.toggleFullscreen
    }

    // append fullscreen action only if the caller do not replace it (it's the case of ToolPage)
    if (this.props.actions instanceof Promise) {
      this.props.actions.then(promisedActions => {
        const fullscreenPos = promisedActions.findIndex(action => 'fullscreen' === action.name)
        if (-1 !== fullscreenPos) {
          this.setState({actions: promisedActions})
        } else {
          this.setState({actions: promisedActions.concat([fullscreenAction])})
        }
      })
    } else {
      const fullscreenPos = (this.props.actions || []).findIndex(action => 'fullscreen' === action.name)
      if (-1 !== fullscreenPos) {
        this.setState({actions: (this.props.actions || [])})
      } else {
        this.setState({actions: (this.props.actions || []).concat([fullscreenAction])})
      }
    }
  }

  toggleFullscreen() {
    this.setState({fullscreen: !this.state.fullscreen})
  }

  render() {
    return (
      <PageSimple
        {...omit(this.props, 'showHeader', 'showTitle', 'header', 'title', 'subtitle', 'icon', 'poster', 'toolbar', 'actions', 'fullscreen')}
        fullscreen={this.state.fullscreen}
        meta={merge({}, {
          title: this.props.title,
          poster: this.props.poster
        }, this.props.meta || {})}
      >
        {this.state.fullscreen && 0 !== this.state.actions.length &&
          <Button
            className="fullscreen-close"
            {...this.state.actions.find(action => 'fullscreen' === action.name)}
            tooltip="bottom"
          />
        }

        {!this.state.fullscreen && this.props.showHeader &&
          <PageHeader
            id={this.props.id}
            showTitle={this.props.showTitle}
            title={this.props.title}
            subtitle={this.props.subtitle}
            icon={this.props.icon}
            poster={this.props.poster}
            toolbar={this.props.toolbar}
            disabled={this.props.disabled}
            actions={this.state.actions}
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
