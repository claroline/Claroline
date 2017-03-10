import React, {PropTypes as T} from 'react'
import {asset} from '#/main/core/asset'

export const AudioContentThumbnail = (props) =>
  <div className="audio-content-thumbnail">
    {props.data &&
      <audio
        onClick={e => e.stopPropagation()}
        controls
      >
        <source src={asset(props.data)} type={props.type}/>
      </audio>
    }
  </div>

AudioContentThumbnail.propTypes = {
  data: T.string,
  type: T.string.isRequired
}
