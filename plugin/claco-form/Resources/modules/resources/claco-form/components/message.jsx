import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {HtmlText} from '#/main/core/layout/components/html-text.jsx'

import {actions} from '#/plugin/claco-form/resources/claco-form/actions'

class MessageComponent extends Component {
  componentWillUnmount() {
    this.props.resetMessage()
  }

  render() {
    return (
      <div>
        {this.props.message && this.props.message.content &&
          <div className={`alert alert-${this.props.message.type}`}>
            <i
              className="fa fa-times close"
              onClick={() => this.props.resetMessage()}
            >
            </i>
            <HtmlText>
              {this.props.message.content}
            </HtmlText>
          </div>
        }
      </div>
    )
  }
}

MessageComponent.propTypes = {
  message: T.shape({
    content: T.string,
    type: T.string
  }),
  resetMessage: T.func.isRequired
}

const Message = connect(
  (state) => ({
    message: state.message
  }),
  (dispatch) => ({
    resetMessage() {
      dispatch(actions.resetMessage())
    }
  })
)(MessageComponent)

export {
  Message
}