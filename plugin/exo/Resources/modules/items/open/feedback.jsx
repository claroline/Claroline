import React, {PropTypes as T} from 'react'

import {tex} from '../../utils/translate'

export const OpenFeedback = props =>
  <div className="open-feedback">
    {props.answer && 0 !== props.answer.length ?
      <div dangerouslySetInnerHTML={{__html: props.answer}} />
      : <div className="no-answer">{tex('no_answer')}</div>
    }
  </div>

OpenFeedback.propTypes = {
  answer: T.string
}

OpenFeedback.defaultProps = {
  answer: ''
}
