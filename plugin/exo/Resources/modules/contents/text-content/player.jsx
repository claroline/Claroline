import React from 'react'
import {PropTypes as T} from 'prop-types'

export const TextContentPlayer = (props) =>
  <div dangerouslySetInnerHTML={{ __html: props.item.data }}>
  </div>

TextContentPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    data: T.string.isRequired
  }).isRequired
}
