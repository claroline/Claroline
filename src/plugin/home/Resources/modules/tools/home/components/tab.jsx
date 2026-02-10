import React, {Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {getTab} from '#/plugin/home/home'
import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {HomePageSkeleton} from '#/plugin/home/tools/home/components/page'
import {HomeError} from '#/plugin/home/tools/home/components/error'
import {ErrorBoundary} from '#/main/app/components/error-boundary'

class HomeTab extends Component {
  constructor(props) {
    super(props)

    this.state = {
      component: null
    }
  }

  componentDidMount() {
    if (this.props.currentTab) {
      if (!this.props.loaded) {
        this.props.open(this.props.currentTab)
      }

      getTab(this.props.currentTab.type).then(tabApp => this.setState({
        component: tabApp.component
      }))
    }
  }

  componentDidUpdate(prevProps) {
    if (this.props.currentTab) {
      if (this.props.currentTab.slug !== get(prevProps, 'currentTab.slug') || (prevProps.loaded !== this.props.loaded && !this.props.loaded)) {
        this.props.open(this.props.currentTab)
      }

      if (get(prevProps, 'currentTab.type') !== get(this.props, 'currentTab.type')) {
        getTab(this.props.currentTab.type).then(tabApp => this.setState({
          component: tabApp.component
        }))
      }
    }
  }

  render() {
    if (!this.props.loaded) {
      return (
        <HomePageSkeleton />
      )
    }

    if (!isEmpty(this.props.error)) {
      return (
        <HomeError {...this.props.error} />
      )
    }

    if (this.props.currentTab && this.state.component) {
      return (
        <ErrorBoundary fallback={<HomeError code="UNKNOWN_ERROR" message="Error while rendering the requested home tab." />}>
          {createElement(this.state.component, {
            path: `${this.props.path}/${this.props.currentTab.slug}`,
            currentContext: this.props.currentContext,
            currentTab: this.props.currentTab
          })}
        </ErrorBoundary>
      )
    }

    return null
  }
}

HomeTab.propTypes = {
  path: T.string.isRequired,
  loaded: T.bool.isRequired,
  currentContext: T.object.isRequired,
  currentTab: T.shape(TabTypes.propTypes),
  error: T.object,
  open: T.func.isRequired
}

export {
  HomeTab
}
