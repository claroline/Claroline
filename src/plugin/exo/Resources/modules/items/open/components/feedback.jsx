import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Html} from '#/main/app/components/html'
import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'

const OpenFeedback = props =>
  <div className="open-feedback">
    {props.feedback &&
      <div className="pull-right">
        <Feedback
          id={props.item.id}
          feedback={props.feedback}
        />
      </div>
    }

    {props.answer && 0 !== props.answer.length ?
      <Html>{props.answer}</Html>
      :
      <div className="no-answer">{trans('no_answer', {}, 'quiz')}</div>
    }
  </div>

OpenFeedback.propTypes = {
  item: T.shape({
    id: T.string.isRequired
  }).isRequired,
  answer: T.string,
  feedback: T.string
}

export {
  OpenFeedback
}
