import React, {createElement, Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'

import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {route as resourceRoute} from '#/main/core/resource/routing'

import {constants} from '#/plugin/history/header/history/constants'

const HistoryDropdown = props =>
  <div className="app-header-dropdown dropdown-menu dropdown-menu-right data-cards-stacked">
    <ul className="nav nav-tabs">
      <li className={classes({
        active: 'workspaces' === props.section
      })}>
        <a
          role="button"
          href=""
          onClick={(e) => {
            e.preventDefault()
            props.changeSection('workspaces')
          }}
        >
          {trans('workspaces')}
        </a>
      </li>
      <li className={classes({
        active: 'resources' === props.section
      })}>
        <a
          role="button"
          href=""
          onClick={(e) => {
            e.preventDefault()
            props.changeSection('resources')
          }}
        >
          {trans('resources')}
        </a>
      </li>
    </ul>

    {isEmpty(props.results) &&
      <div className="app-header-dropdown-empty">
        {trans('workspaces' === props.section ? 'empty_workspaces':'empty_resources', {}, 'history')}
        <small>
          {trans('workspaces' === props.section ? 'empty_workspaces_help':'empty_resources_help', {}, 'history')}
        </small>
      </div>
    }

    {!isEmpty(props.results) && props.results.map(result =>
      createElement(constants.RESULTS_CARD[props.section], {
        key: result.id,
        size: 'xs',
        direction: 'row',
        data: result,
        primaryAction: {
          type: LINK_BUTTON,
          label: trans('open', {}, 'actions'),
          target: 'workspaces' === props.section ? workspaceRoute(result) : resourceRoute(result),
          onClick: props.closeMenu
        }
      })
    )}

    <div className="app-header-dropdown-footer">
      <Button
        className="btn-link btn-emphasis btn-block"
        type={LINK_BUTTON}
        label={trans('workspaces' === props.section ? 'all_workspaces' : 'all_resources', {}, 'history')}
        target={toolRoute('workspaces' === props.section ? 'workspaces' : 'resources')}
        primary={true}
        onClick={props.closeMenu}
      />
    </div>
  </div>

HistoryDropdown.propTypes = {
  section: T.oneOf(['resources', 'workspaces']),
  results: T.array,
  changeSection: T.func.isRequired,
  closeMenu: T.func.isRequired
}

class HistoryMenu extends Component {
  constructor(props) {
    super(props)

    this.state = {
      opened: false,
      section: 'workspaces'
    }

    this.changeSection = this.changeSection.bind(this)
    this.setOpened = this.setOpened.bind(this)
  }

  changeSection(section) {
    this.setState({section: section})
  }

  setOpened(opened) {
    this.setState({opened: opened})
  }

  render() {
    if (!this.props.isAuthenticated) {
      return null
    }

    return (
      <Button
        id="app-history"
        type={MENU_BUTTON}
        className="app-header-btn app-header-item"
        icon={!this.props.loaded && this.state.opened ?
          'fa fa-fw fa-spinner fa-spin' :
          'fa fa-fw fa-history'
        }
        label={trans('history', {}, 'history')}
        tooltip="bottom"
        opened={this.props.loaded && this.state.opened}
        onToggle={(opened) => {
          if (opened) {
            this.props.getHistory()
          }

          this.setOpened(opened)
        }}
        menu={
          <HistoryDropdown
            section={this.state.section}
            results={!isEmpty(this.props.results) ? this.props.results[this.state.section] : []}
            changeSection={this.changeSection}
            closeMenu={() => this.setOpened(false)}
          />
        }
      />
    )
  }
}

HistoryMenu.propTypes = {
  isAuthenticated: T.bool.isRequired,
  loaded: T.bool.isRequired,
  results: T.object,
  getHistory: T.func.isRequired
}

export {
  HistoryMenu
}
