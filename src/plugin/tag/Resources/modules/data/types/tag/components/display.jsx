import React from 'react'
import {PropTypes as T} from 'prop-types'

import {toKey} from '#/main/core/scaffolding/text'

const TagDisplay = (props) =>
  <div className="tag-display">
    {props.data.map(tag =>
      <span key={toKey(tag)} className="tag badge text-bg-primary">{tag}</span>
    )}
  </div>

TagDisplay.propTypes = {
  data: T.arrayOf(T.string).isRequired
}

TagDisplay.defaultProps = {
  data: []
}

export {
  TagDisplay
}
