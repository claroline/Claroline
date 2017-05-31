import React from 'react'
import {PropTypes as T} from 'prop-types'
import {asset} from '#/main/core/asset'

export const AudioContentPlayer = (props) =>
  <div className="audio-item-content">
    <audio controls>
      <source src={asset(props.item.url)} type={props.item.type} />
    </audio>
  </div>

AudioContentPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    type: T.string.isRequired,
    url: T.string.isRequired
  }).isRequired
}
