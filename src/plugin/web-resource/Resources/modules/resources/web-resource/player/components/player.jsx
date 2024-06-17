import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {asset} from '#/main/app/config/asset'

import {selectors} from '#/plugin/web-resource/resources/web-resource/store'
import {ResourcePage} from '#/main/core/resource'

class PlayerComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      height: 0
    }

    this.checkHeight = this.checkHeight.bind(this)
  }

  checkHeight() {
    let contentHeight = this.iframe.contentWindow.document.body.scrollHeight

    if (contentHeight === 0) {
      // dirty, but we need this element if everything is populated through javascript in the iframe...
      contentHeight = document.getElementsByClassName('page-content')[0].clientHeight
    }

    if (contentHeight !== this.state.height) {
      this.setState({height: contentHeight})
    }
  }

  handleResize() {
    window.setInterval(this.checkHeight, 3000)
  }

  render() {
    return (
      <ResourcePage>
        <iframe
          className="web-resource"
          ref={el => this.iframe = el}
          onLoad={this.handleResize()}
          height={this.state.height}
          src={asset(this.props.resourcePath)}
          allowFullScreen={true}
        />
      </ResourcePage>
    )
  }
}

PlayerComponent.propTypes = {
  resourcePath: T.string.isRequired
}

const Player = connect(
  state => ({
    resourcePath: selectors.path(state)
  })
)(PlayerComponent)

export {
  Player
}
