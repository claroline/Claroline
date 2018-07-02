import React, {Component} from 'react'
import {connect} from 'react-redux'

import {asset} from '#/main/core/scaffolding/asset'
import {selectors as resourceSelector} from '#/main/core/resource/store'

import {select} from '#/plugin/web-resource/resources/web-resource/selectors'

class PlayerComponent extends Component {
  constructor(props) {
    super(props)
    this.checkHeight = this.checkHeight.bind(this)
    this.state = {
      height: 0
    }
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
        src={asset(`uploads/webresource/${this.props.workspaceId}/${this.props.path}`)}
        allowFullScreen={true}
      />
    )
  }
}

const Player = connect(
  state => ({
    path: select.path(state),
    workspaceId: resourceSelector.resourceNode(state).workspace.id
  })
)(PlayerComponent)

export {
  Player
}
