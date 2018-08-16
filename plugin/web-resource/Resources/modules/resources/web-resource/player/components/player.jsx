import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {asset} from '#/main/core/scaffolding/asset'

import {selectors} from '#/plugin/web-resource/resources/web-resource/store'

class PlayerComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      height: 0
    }

    this.checkHeight = this.checkHeight.bind(this)
  }

  checkHeight() {
    const contentHeight = this.iframe.contentWindow.document.body.scrollHeight
    if (contentHeight !== this.state.height) {
      this.setState({height: contentHeight})
    }
  }

  handleResize() {
    window.setInterval(this.checkHeight, 3000)
  }

  render() {
    return (
      <iframe
        className="web-resource"
        ref={el => this.iframe = el}
        onLoad={this.handleResize()}
        height={this.state.height}
        src={asset(this.props.path)}
        allowFullScreen={true}
      />
    )
  }
}

PlayerComponent.propTypes = {
  path: T.string.isRequired
}

const Player = connect(
  state => ({
    path: selectors.path(state)
  })
)(PlayerComponent)

export {
  Player
}
