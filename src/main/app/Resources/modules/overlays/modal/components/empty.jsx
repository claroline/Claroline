import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import BaseModal from 'react-bootstrap/Modal'

class ModalEmpty extends Component {
  constructor(props) {
    super(props)

    this.state = {
      enterEvents: this.props.show,
      exitEvents: true
    }

    this.onHide = this.onHide.bind(this)
    this.onEnter = this.onEnter.bind(this)
    this.onEntering = this.onEntering.bind(this)
    this.onEntered = this.onEntered.bind(this)
    this.onExit = this.onExit.bind(this)
    this.onExiting = this.onExiting.bind(this)
    this.onExited = this.onExited.bind(this)
  }

  componentDidUpdate(prevProps) {
    if (this.props.show !== prevProps.show) {
      // we are showing / hiding the modal,
      // we need to enable lifecycle events
      this.setState({
        enterEvents: this.props.show,
        exitEvents: !this.props.show
      })
    }

    if (this.props.disabled !== prevProps.disabled) {
      // we are enabling / disabling the modal,
      // we need to disable lifecycle events
      this.setState({
        enterEvents: false,
        exitEvents: !this.props.disabled
      })
    }
  }

  on(event, eventCallback) {
    // only trigger enter events if needed
    if (eventCallback && this.state[`${event}Events`]) {
      eventCallback()
    }
  }

  onHide() {
    this.on('exit', this.props.fadeModal)
  }

  onEnter() {
    this.on('enter', this.props.onEnter)
  }

  onEntering() {
    this.on('enter', this.props.onEntering)
  }

  onEntered() {
    this.on('enter', this.props.onEntered)
  }

  onExit() {
    this.on('exit', this.props.onExit)
  }

  onExiting() {
    this.on('exit', this.props.onExiting)
  }

  onExited() {
    this.on('exit', this.props.onExited)
    this.on('exit', this.props.hideModal)
  }

  render() {
    return (
      <BaseModal
        {...omit(this.props, 'fadeModal', 'hideModal', 'closeButton', 'className', 'children')}
        autoFocus={true}
        enforceFocus={false}
        dialogClassName={this.props.className}
        fullscreen={this.props.fullscreen}
        show={this.props.show && !this.props.disabled}

        onHide={this.onHide}
        onEnter={this.onEnter}
        onEntering={this.onEntering}
        onEntered={this.onEntered}
        onExit={this.onExit}
        onExiting={this.onExiting}
        onExited={this.onExited}
      >
        {this.props.children}
      </BaseModal>
    )
  }
}

ModalEmpty.propTypes = {
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired,
  show: T.bool.isRequired,
  closeButton: T.bool,
  size: T.oneOf(['sm', 'lg', 'xl']),
  fullscreen: T.oneOf([true, 'sm-down','md-down', 'lg-down', 'xl-down', 'xxl-down']),
  // modal events (from react-bootstrap)
  onEnter: T.func,
  onEntering: T.func,
  onEntered: T.func,
  onExit: T.func,
  onExiting: T.func,
  onExited: T.func,

  // a modal is disabled when another one is opened
  // over it. In this case we want to hide without trigger lifecycle events
  disabled: T.bool,

  className: T.string,
  children: T.node.isRequired
}

ModalEmpty.defaultProps = {
  disabled: true,
  closeButton: true
}

export {
  ModalEmpty
}
