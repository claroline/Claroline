import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'
import {actions} from '../actions'

class Message extends Component {
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

Message.propTypes = {
  message: T.shape({
    content: T.string,
    type: T.string
  }),
  resetMessage: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    message: state.message
  }
}

function mapDispatchToProps(dispatch) {
  return {
    resetMessage: () => dispatch(actions.resetMessage())
  }
}

const ConnectedMessage = connect(mapStateToProps, mapDispatchToProps)(Message)

export {ConnectedMessage as Message}