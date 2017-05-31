import React from 'react'
import {PropTypes as T} from 'prop-types'
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
