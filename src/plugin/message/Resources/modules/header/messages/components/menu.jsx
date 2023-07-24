import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_MESSAGES} from '#/plugin/message/modals/messages'

class MessagesMenu extends Component {
  constructor(props) {
    super(props)

    this.count = this.count.bind(this)

    if (this.props.isAuthenticated) {
      this.count()
    }
  }

  componentDidUpdate(prevProps) {
    if (prevProps.isAuthenticated !== this.props.isAuthenticated) {
      if (this.props.isAuthenticated) {
        this.count()
      } else {
        this.stopCount()
      }
    }
  }

  componentWillUnmount() {
    this.stopCount()
  }

  count() {
    this.props.countMessages()
      .then(() => {
        if (this.props.refreshDelay) {
          this.counter = setTimeout(this.count, this.props.refreshDelay)
        }
      })
  }

  stopCount() {
    if (this.counter) {
      clearTimeout(this.counter)
    }
  }

  render() {
    if (!this.props.isAuthenticated) {
      return null
    }

    return (
      <Button
        id="app-messages"
        type={MODAL_BUTTON}
        className="app-header-btn app-header-item"
        icon="fa fa-fw fa-envelope"
        label={trans('messages', {}, 'message')}
        tooltip="bottom"
        modal={[MODAL_MESSAGES, {
          count: this.props.count
        }]}
        subscript={0 !== this.props.count ? {
          type: 'label',
          status: 'primary',
          value: 100 > this.props.count ? this.props.count : '99+'
        } : undefined}
      />
    )
  }
}

MessagesMenu.propTypes = {
  isAuthenticated: T.bool.isRequired,
  refreshDelay: T.number,
  count: T.number.isRequired,
  countMessages: T.func.isRequired
}

export {
  MessagesMenu
}
