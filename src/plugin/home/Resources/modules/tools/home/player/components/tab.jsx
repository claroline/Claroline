import {Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {getTab} from '#/plugin/home/home'
import {Tab as TabTypes} from '#/plugin/home/prop-types'

class PlayerTab extends Component {
  constructor(props) {
    super(props)

    this.state = {
      component: null
    }
  }

  componentDidMount() {
    if (this.props.currentTab) {
      getTab(this.props.currentTab.type).then(tabApp => this.setState({
        component: tabApp.component
      }))
    }
  }

  componentDidUpdate(prevProps) {
    if (this.props.currentTab && get(prevProps, 'currentTab.type') !== get(this.props, 'currentTab.type')) {
      getTab(this.props.currentTab.type).then(tabApp => this.setState({
        component: tabApp.component
      }))
    }
  }

  render() {
    if (this.props.currentTab && this.state.component) {
      return createElement(this.state.component, {
        path: `${this.props.path}/${this.props.currentTab.slug}`,
        currentContext: this.props.currentContext,
        tabs: this.props.tabs,
        currentTab: this.props.currentTab,
        title: this.props.currentTabTitle
      })
    }

    return null
  }
}

PlayerTab.propTypes = {
  path: T.string.isRequired,
  currentContext: T.object.isRequired,
  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentTabTitle: T.string.isRequired,
  currentTab: T.shape(TabTypes.propTypes)
}

export {
  PlayerTab
}
