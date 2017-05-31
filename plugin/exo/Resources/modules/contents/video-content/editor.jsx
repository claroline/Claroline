import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/core/asset'
//import videojs from 'video.js'

class VideoPlayer extends Component {
  //componentDidMount() {
  //  this.player = videojs(this.videoNode, this.props)
  //}

  //componentWillUnmount() {
  //  if (this.player) {
  //    this.player.dispose()
  //  }
  //}

  render() {
    return (
      <video ref={ node => this.videoNode = node } className="not-video-js vjs-big-play-centered vjs-default-skin vjs-16-9" controls>
        <source src={(this.props.item.data && asset(this.props.item.data)) || (this.props.item.url && asset(this.props.item.url)) || ''}
                type={this.props.item.type}
        />
      </video>
    )
  }
}

VideoPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    type: T.string.isRequired,
    data: T.string,
    url: T.string
  }).isRequired
}

export const VideoContent = (props) =>
  <div className="video-item-content">
    {(props.item.data || props.item.url) &&
      <VideoPlayer { ...props} />
    }
  </div>

VideoContent.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    type: T.string.isRequired,
    data: T.string,
    url: T.string
  }).isRequired
}
