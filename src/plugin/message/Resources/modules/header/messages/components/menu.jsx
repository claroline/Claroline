import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'
import {route as toolRoute} from '#/main/core/tool/routing'

import {Message as MessageTypes} from '#/plugin/message/prop-types'
import {MessageCard} from '#/plugin/message/data/components/message-card'
import {constants} from '#/plugin/message/header/messages/constants'

const MessagesDropdown = (props) =>
  <div className="app-header-dropdown dropdown-menu dropdown-menu-right data-cards-stacked">
    {isEmpty(props.results) &&
      <div className="app-header-dropdown-empty">
        {trans('empty_unread', {}, 'message')}
        <small>
          {trans('empty_unread_help', {}, 'message')}
        </small>
      </div>
    }

    {!isEmpty(props.results) && props.results.map(result =>
      <MessageCard
        key={result.id}
        size="xs"
        direction="row"
        data={result}
        primaryAction={{
          type: LINK_BUTTON,
          label: trans('open', {}, 'actions'),
          target: toolRoute('messaging') + '/message/' + result.id,
          onClick: props.closeMenu
        }}
      />
    )}

    {props.count > constants.LIMIT_RESULTS &&
      <div className="app-header-dropdown-footer app-header-dropdown-empty">
        {trans('more_unread', {count: props.count - constants.LIMIT_RESULTS}, 'message')}
      </div>
    }

    <div className="app-header-dropdown-footer">
      <Button
        className="btn-link btn-emphasis btn-block"
        type={LINK_BUTTON}
        label={trans('all_messages', {}, 'message')}
        target={toolRoute('messaging')}
        primary={true}
        onClick={props.closeMenu}
      />
    </div>
  </div>

MessagesDropdown.propTypes = {
  count: T.number.isRequired,
  results: T.arrayOf(T.shape(
    MessageTypes.propTypes
  )).isRequired,
  closeMenu: T.func.isRequired
}

class MessagesMenu extends Component {
  constructor(props) {
    super(props)

    this.state = {
      opened: false
    }

    this.count = this.count.bind(this)
    this.setOpened = this.setOpened.bind(this)

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

  setOpened(opened) {
    this.setState({opened: opened})
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
        type={MENU_BUTTON}
        className="app-header-btn app-header-item"
        icon={!this.props.loaded && this.state.opened ?
          'fa fa-fw fa-spinner fa-spin' :
          'fa fa-fw fa-envelope'
        }
        label={trans('messages', {}, 'message')}
        tooltip="bottom"
        opened={this.props.loaded && this.state.opened}
        onToggle={(opened) => {
          if (opened) {
            this.props.getMessages()
          }

          this.setOpened(opened)
        }}
        menu={
          <MessagesDropdown
            count={this.props.count}
            results={this.props.results}
            closeMenu={() => this.setOpened(false)}
          />
        }
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
  loaded: T.bool.isRequired,
  results: T.arrayOf(T.shape(
    MessageTypes.propTypes
  )).isRequired,
  countMessages: T.func.isRequired,
  getMessages: T.func.isRequired
}

export {
  MessagesMenu
}
