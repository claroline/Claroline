import React, {PropTypes as T} from 'react'
import {asset} from '#/main/core/asset'

export const VideoContentPlayer = (props) =>
  <div className="video-item-content">
    <video className="not-video-js vjs-big-play-centered vjs-default-skin vjs-16-9" controls>
      <source src={asset(props.item.url)} type={props.item.type} />
    </video>
  </div>

VideoContentPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    type: T.string.isRequired,
    url: T.string.isRequired
  }).isRequired
}
