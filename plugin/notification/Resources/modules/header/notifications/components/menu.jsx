import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'
import {route as toolRoute} from '#/main/core/tool/routing'

import {NotificationCard} from '#/plugin/notification/components/card'
import {constants} from '#/plugin/notification/header/notifications/constants'

const NotificationsDropdown = (props) =>
  <div className="app-header-dropdown dropdown-menu dropdown-menu-right">
    {isEmpty(props.results) &&
      <div className="app-header-dropdown-empty">
        {trans('empty_unread', {}, 'notification')}
        <small>
          {trans('empty_unread_help', {}, 'notification')}
        </small>
      </div>
    }

    {!isEmpty(props.results) && props.results.map(result =>
      <NotificationCard
        key={result.id}
        size="xs"
        direction="row"
        data={result}
      />
    )}

    {props.count > constants.LIMIT_RESULTS &&
      <div className="app-header-dropdown-footer app-header-dropdown-empty">
        {trans('more_unread', {count: props.count - constants.LIMIT_RESULTS}, 'notification')}
      </div>
    }

    <div className="app-header-dropdown-footer">
      <Button
        className="btn-link btn-emphasis btn-block"
        type={LINK_BUTTON}
        label={trans('all_notifications', {}, 'notification')}
        target={toolRoute('notification')}
        primary={true}
      />
    </div>
  </div>

NotificationsDropdown.propTypes = {
  count: T.number.isRequired,
  results: T.arrayOf(T.shape({
    // TODO
  })).isRequired
}

class NotificationsMenu extends Component {
  constructor(props) {
    super(props)

    this.state = {
      opened: false
    }

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
    this.props.countNotifications()
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
        id="app-favorites"
        type={MENU_BUTTON}
        className="app-header-btn app-header-item"
        icon={!this.props.loaded && this.state.opened ?
          'fa fa-fw fa-spinner fa-spin' :
          'fa fa-fw fa-bell'
        }
        label={trans('notifications', {}, 'notification')}
        tooltip="bottom"
        opened={this.props.loaded && this.state.opened}
        onToggle={(opened) => {
          if (opened) {
            this.props.getNotifications()
          }

          this.setState({opened: opened})
        }}
        menu={
          <NotificationsDropdown
            count={this.props.count}
            results={this.props.results}
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

NotificationsMenu.propTypes = {
  isAuthenticated: T.bool.isRequired,
  refreshDelay: T.number,
  count: T.number.isRequired,
  loaded: T.bool.isRequired,
  results: T.arrayOf(T.shape({
    // TODO
  })).isRequired,
  countNotifications: T.func.isRequired,
  getNotifications: T.func.isRequired
}

export {
  NotificationsMenu
}
