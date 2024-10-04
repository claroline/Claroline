import React, {Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ContentLoader} from '#/main/app/content/components/loader'

import {getTab} from '#/plugin/home/home'
import {Tab as TabTypes} from '#/plugin/home/prop-types'
import {HomePage} from '#/plugin/home/tools/home/components/page'
import {HomeRestrictions} from '#/plugin/home/tools/home/components/restrictions'

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
        <HomePage
          currentTab={this.props.currentTab}
          title={this.props.currentTabTitle}
          root={this.props.root}
        >
          <ContentLoader
            size="lg"
            description={trans('loading', {}, 'home')}
          />
        </HomePage>
      )
    }

    if (!isEmpty(this.props.accessErrors)) {
      return (
        <HomePage
          currentTab={this.props.currentTab}
          title={this.props.currentTabTitle}
          root={this.props.root}
        >
          <HomeRestrictions
            errors={this.props.accessErrors}
            dismiss={this.props.dismissRestrictions}
            managed={this.props.managed}
            checkAccessCode={(code) => this.props.checkAccessCode(this.props.currentTab, code)}
          />
        </HomePage>
      )
    }

    if (this.props.currentTab && this.state.component) {
      return createElement(this.state.component, {
        path: `${this.props.path}/${this.props.currentTab.slug}`,
        currentContext: this.props.currentContext,
        currentTab: this.props.currentTab,
        title: this.props.currentTabTitle,
        root: this.props.root
      })
    }

    return null
  }
}

HomeTab.propTypes = {
  path: T.string.isRequired,
  loaded: T.bool.isRequired,
  root: T.bool,
  currentContext: T.object.isRequired,
  currentTabTitle: T.string.isRequired,
  currentTab: T.shape(TabTypes.propTypes),
  managed: T.bool.isRequired,
  accessErrors: T.object,
  open: T.func.isRequired,
  dismissRestrictions: T.func.isRequired,
  checkAccessCode: T.func.isRequired
}

export {
  HomeTab
}
