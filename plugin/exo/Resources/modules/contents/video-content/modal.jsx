import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/core/asset'

export const VideoContentModal = (props) =>
  <div className="video-content-modal">
    {props.data &&
      <video className="not-video-js vjs-big-play-centered vjs-default-skin vjs-16-9" controls>
        <source src={asset(props.data)} type={props.type}/>
      </video>
    }
  </div>

VideoContentModal.propTypes = {
  data: T.string,
  type: T.string.isRequired
}
