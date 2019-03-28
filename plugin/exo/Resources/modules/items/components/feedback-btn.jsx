import React from 'react'
import {PropTypes as T} from 'prop-types'
import Popover from 'react-bootstrap/lib/Popover'
import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import classes from 'classnames'

import {HtmlText} from '#/main/core/layout/components/html-text'

const Feedback = props => {
  if (!props.feedback) {
    return (
      <span className="feedback-btn" />
    )
  }

  return (
    <OverlayTrigger
      trigger="click"
      placement="bottom"
      rootClose={true}
      overlay={
        <Popover id={props.id} className="feedback-popover">
          <HtmlText>{props.feedback}</HtmlText>
        </Popover>
      }
    >
      <button type="button" className={classes('btn', 'btn-link-default', 'feedback-btn', props.className)}>
        <span className="fa fa-fw fa-comments-o" />
      </button>
    </OverlayTrigger>
  )
}

Feedback.propTypes = {
  className: T.string,
  feedback: T.string,
  id: T.any
}

export {
  Feedback
}
