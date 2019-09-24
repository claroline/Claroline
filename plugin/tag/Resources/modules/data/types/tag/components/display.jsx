import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {toKey} from '#/main/core/scaffolding/text'

const TagDisplay = (props) =>
  <Fragment>
    {props.data.map(tag =>
      <span key={toKey(tag)} className="label label-info">{tag}</span>
    )}
  </Fragment>

TagDisplay.propTypes = {
  data: T.arrayOf(T.string).isRequired
}

TagDisplay.defaultProps = {
  data: []
}

export {
  TagDisplay
}
