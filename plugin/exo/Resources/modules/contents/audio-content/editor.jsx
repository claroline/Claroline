import React from 'react'
import {PropTypes as T} from 'prop-types'
import {asset} from '#/main/core/asset'

export const AudioContent = (props) =>
  <div className="audio-item-content">
    {(props.item.data || props.item.url) &&
      <audio controls>
        <source src={(props.item.data && asset(props.item.data)) || (props.item.url && asset(props.item.url)) || ''}
                type={props.item.type}
        />
      </audio>
    }
  </div>

AudioContent.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    type: T.string.isRequired,
    data: T.string,
    url: T.string
  }).isRequired
}
