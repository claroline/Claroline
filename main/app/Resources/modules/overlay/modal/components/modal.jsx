import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import BaseModal from 'react-bootstrap/lib/Modal'

// TODO : implements modal actions

class Modal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      enterEvents: this.props.show,
      exitEvents: true
    }
  }

  componentWillReceiveProps(nextProps) {
    if (this.props.show !== nextProps.show) {
      // we are showing / hiding the modal,
      // we need to enable lifecycle events
      this.setState({
        enterEvents: nextProps.show,
        exitEvents: !nextProps.show
      })
    }

    if (this.props.disabled !== nextProps.disabled) {
      // we are enabling / disabling the modal,
      // we need to disable lifecycle events
      this.setState({
        enterEvents: false,
        exitEvents: !nextProps.disabled
      })
    }
  }

  on(event, eventCallback) {
    // only trigger enter events if needed
    if (eventCallback && this.state[`${event}Events`]) {
      eventCallback()
    }
  }

  render() {
    return (
      <BaseModal
        {...omit(this.props, 'fadeModal', 'hideModal', 'icon', 'title', 'subtitle', 'className', 'children')}
        autoFocus={true}
        enforceFocus={false}
        dialogClassName={this.props.className}
        show={this.props.show && !this.props.disabled}
        onHide={() => this.on('exit', this.props.fadeModal)}

        onEnter={() => this.on('enter', this.props.onEnter)}
        onEntering={() => this.on('enter', this.props.onEntering)}
        onEntered={() => this.on('enter', this.props.onEntered)}

        onExit={() => this.on('exit', this.props.onExit)}
        onExiting={() => this.on('exit', this.props.onExiting)}
        onExited={() => {
          this.on('exit', this.props.onExit)
          this.on('exit', this.props.hideModal)
        }}
      >
        {this.props.title &&
          <BaseModal.Header closeButton={true}>
            <BaseModal.Title>
              {this.props.icon &&
                <span className={classes('modal-icon', this.props.icon)} />
              }

              {this.props.title}

              {this.props.subtitle &&
                <small className={classes({'with-icon': !!this.props.icon})}>{this.props.subtitle}</small>
              }
            </BaseModal.Title>
          </BaseModal.Header>
        }

        {this.props.children}
      </BaseModal>
    )
  }
}

Modal.propTypes = {
  bsSize: T.string,
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired,
  show: T.bool.isRequired,

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

  icon: T.string,
  title: T.string,
  subtitle: T.string,
  className: T.string,
  children: T.node.isRequired
}

Modal.defaultProps = {
  disabled: true
}

export {
  Modal
}
