import React, {PropTypes as T} from 'react'
import Popover from 'react-bootstrap/lib/Popover'
import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'

export const Feedback = props => {
  if (!props.feedback) return <span className="item-feedback"/>

  const popoverClick = (
    <Popover className="item-feedback" id={props.id}>
      <div className="feedback-content" dangerouslySetInnerHTML={{__html: props.feedback}}>
      </div>
    </Popover>
  )

  return(
    <OverlayTrigger trigger="click" placement="top" overlay={popoverClick}>
      <i className="feedback-btn fa fa-comments-o"></i>
    </OverlayTrigger>
  )
}

Feedback.propTypes = {
  feedback: T.string,
  id: T.any
}
