import React, {PropTypes as T} from 'react'
import {asset} from '#/main/core/asset'

export const AudioContentModal = (props) =>
  <div className="audio-content-modal">
    {props.data &&
      <audio controls>
        <source src={asset(props.data)} type={props.type}/>
      </audio>
    }
  </div>

AudioContentModal.propTypes = {
  data: T.string,
  type: T.string.isRequired
}
