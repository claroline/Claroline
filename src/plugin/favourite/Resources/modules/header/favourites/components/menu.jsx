import React, {createElement, Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'

import {route as toolRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {route as resourceRoute} from '#/main/core/resource/routing'

import {constants} from '#/plugin/favourite/header/favourites/constants'

const FavouritesDropdown = props =>
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
        {trans('workspaces' === props.section ? 'empty_workspaces':'empty_resources', {}, 'favourite')}
        <small>
          {trans('workspaces' === props.section ? 'empty_workspaces_help':'empty_resources_help', {}, 'favourite')}
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
        },
        actions: [
          {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-trash-o',
            label: trans('delete', {}, 'actions'),
            callback: () => props.deleteFavourite(result, props.section),
            confirm: {
              title: trans('delete_favorite', {}, 'favourite'),
              subtitle: result.name,
              message: trans('workspaces' === props.section ? 'delete_workspace_message' : 'delete_resource_message', {}, 'favourite')
            },
            dangerous: true
          }
        ]
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

FavouritesDropdown.propTypes = {
  section: T.oneOf(['resources', 'workspaces']),
  results: T.array,
  changeSection: T.func.isRequired,
  deleteFavourite: T.func.isRequired,
  closeMenu: T.func.isRequired
}

class FavouritesMenu extends Component {
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
        id="app-favourites"
        type={MENU_BUTTON}
        className="app-header-btn app-header-item"
        icon={!this.props.loaded && this.state.opened ?
          'fa fa-fw fa-spinner fa-spin' :
          'fa fa-fw fa-star'
        }
        label={trans('favourites', {}, 'favourite')}
        tooltip="bottom"
        opened={this.props.loaded && this.state.opened}
        onToggle={(opened) => {
          if (opened) {
            this.props.getFavourites()
          }

          this.setOpened(opened)
        }}
        menu={
          <FavouritesDropdown
            section={this.state.section}
            results={!isEmpty(this.props.results) ? this.props.results[this.state.section] : []}
            changeSection={this.changeSection}
            deleteFavourite={this.props.deleteFavourite}
            closeMenu={() => this.setOpened(false)}
          />
        }
      />
    )
  }
}

FavouritesMenu.propTypes = {
  isAuthenticated: T.bool.isRequired,
  loaded: T.bool.isRequired,
  results: T.object,
  getFavourites: T.func.isRequired,
  deleteFavourite: T.func.isRequired
}

export {
  FavouritesMenu
}
