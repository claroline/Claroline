import React, {PropTypes as T} from 'react'
import Collapse from 'react-bootstrap/lib/Collapse'

export const SubSection = props =>
  <div className="sub-section">
    {props.hidden &&
      <a role="button" onClick={props.toggle}>
        <span className="fa fa-caret-right"/>
        {props.showText}
      </a>
    }
      <Collapse in={!props.hidden}>
        <div>
          {props.children}
          <a role="button" onClick={props.toggle}>
            <span className="fa fa-caret-right"/>
            {props.hideText}
          </a>
        </div>
      </Collapse>
  </div>

SubSection.propTypes = {
  hidden: T.bool.isRequired,
  showText: T.string.isRequired,
  hideText: T.string.isRequired,
  toggle: T.func.isRequired,
  children: T.any.isRequired
}
