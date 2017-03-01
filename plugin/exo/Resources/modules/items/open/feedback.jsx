import React, {PropTypes as T} from 'react'

export const OpenFeedback = props =>
  <div
    className="open-item-feedback"
    dangerouslySetInnerHTML={{__html: props.answer}}
  />

OpenFeedback.propTypes = {
  answer: T.string
}

OpenFeedback.defaultProps = {
  answer: ''
}
