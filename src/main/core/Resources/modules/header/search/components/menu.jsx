import React, {createElement, Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {Menu} from '#/main/app/overlays/menu/components/menu'

import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {route as resourceRoute} from '#/main/core/resource/routing'
import {route as userRoute} from '#/main/core/user/routing'

import {constants} from '#/main/core/header/search/constants'

class SearchMenu extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentSearch: ''
    }

    this.updateSearch = this.updateSearch.bind(this)
    this.reset = this.reset.bind(this)
  }

  updateSearch(searchStr) {
    this.setState({
      currentSearch: searchStr
    })

    if (searchStr && 3 <= searchStr.length) {
      this.props.search(searchStr)
    }
  }

  reset() {
    this.setState({
      currentSearch: ''
    })
  }

  render() {
    const menuOpened = !isEmpty(this.state.currentSearch) && 3 <= this.state.currentSearch.length && (!this.props.fetching || !this.props.empty)

    return (
      <div className="app-header-search">
        <div className={classes('dropdown', {
          open: menuOpened
        })}>
          <input
            type="search"
            className="form-control input-lg"
            placeholder={trans('search', {}, 'actions')}
            value={this.state.currentSearch}
            onChange={(e) => this.updateSearch(e.target.value)}
          />

          {this.props.fetching &&
            <span className="app-header-search-loader fa fa-fw fa-spinner fa-spin"/>
          }

          {!this.props.fetching && menuOpened &&
            <Button
              className="app-header-search-close"
              type={CALLBACK_BUTTON}
              icon="fa fa-fw fa-times"
              label={trans('close', {}, 'actions')}
              tooltip="bottom"
              callback={this.reset}
            />
          }

          <Menu
            className="app-header-dropdown dropdown-menu dropdown-menu-full"
            open={menuOpened}
            onClose={this.reset}
          >
            {this.props.empty &&
              <div className="app-header-dropdown-empty">
                {trans('no_search_results')}
                <small>
                  {trans('no_search_results_help')}
                </small>
              </div>
            }

            {!this.props.empty && Object.keys(this.props.results)
              .filter(resultType => !isEmpty(this.props.results[resultType]))
              .map(resultType =>
                <Fragment key={resultType}>
                  <h2 className="h5 result-header">{trans(resultType)}</h2>

                  {this.props.results[resultType].map(result =>
                    createElement(constants.RESULTS_CARD[resultType], {
                      key: result.id,
                      size: 'xs',
                      direction: 'row',
                      data: result,
                      primaryAction: {
                        type: LINK_BUTTON,
                        label: trans('open', {}, 'actions'),
                        target: 'workspaces' === resultType ? workspaceRoute(result) : ('resources' === resultType ? resourceRoute(result) : userRoute(result)),
                        onClick: this.reset
                      }
                    })
                  )}
                </Fragment>
              )
            }
          </Menu>
        </div>
      </div>
    )
  }
}

SearchMenu.propTypes = {
  fetching: T.bool.isRequired,
  results: T.object,
  empty: T.bool.isRequired,
  search: T.func.isRequired
}

export {
  SearchMenu
}
