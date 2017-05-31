import React from 'react'
import {PropTypes as T} from 'prop-types'
import Popover from 'react-bootstrap/lib/Popover'
import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import classes from 'classnames'

export const Feedback = props => {
  if (!props.feedback) return <span className="feedback-btn" />

  const popoverClick = (
    <Popover className="feedback-popover" id={props.id}>
      <div dangerouslySetInnerHTML={{__html: props.feedback}}></div>
    </Popover>
  )

  return(
    <OverlayTrigger trigger="click" placement="bottom" overlay={popoverClick} rootClose={true}>
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
