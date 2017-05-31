import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import Popover from 'react-bootstrap/lib/Popover'
import Overlay from 'react-bootstrap/lib/Overlay'

export class HoverFeedback extends Component {
  constructor(props) {
    super(props)
    this.state = {
      show: false
    }
  }

  render() {
    return (
      <span style={{position: 'relative'}}>
        <span
          ref={el => this.el = el}
          className="fa fa-fw fa-comments-o"
          onMouseOver={() => this.setState({show: true})}
          onMouseLeave={() => this.setState({show: false})}
        />
        <Overlay
          show={this.state.show}
          placement="top"
          container={this}
          target={this.el}
        >
          <Popover
            id={this.props.id}
            className="feedback-popover"
          >
            <span dangerouslySetInnerHTML={{ __html: this.props.feedback}}/>
          </Popover>
        </Overlay>
      </span>
    )
  }
}

HoverFeedback.propTypes = {
  id: T.string.isRequired,
  feedback: T.string.isRequired
}
