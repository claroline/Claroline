import React, {createElement, Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Menu} from '#/main/app/overlays/menu/components/menu'

import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {route as resourceRoute} from '#/main/core/resource/routing'
import {route as userRoute} from '#/main/core/user/routing'

import {constants} from '#/main/core/header/search/constants'

class SearchMenu extends Component {
  constructor(props) {
    super(props)

    this.state = {
      menuOpened: false,
      currentSearch: ''
    }

    this.updateSearch = this.updateSearch.bind(this)
  }

  updateSearch(searchStr) {
    this.setState({
      menuOpened: !isEmpty(searchStr) && 3 <= searchStr.length,
      currentSearch: searchStr
    })

    if (searchStr && 3 <= searchStr.length) {
      this.props.search(searchStr)
    }
  }

  render() {
    return (
      <div className="app-header-search">
        <div className={classes('dropdown', {
          open: this.state.menuOpened
        })}>
          <input
            type="search"
            className="form-control input-lg"
            placeholder={trans('search', {}, 'actions')}
            value={this.state.currentSearch}
            onChange={(e) => this.updateSearch(e.target.value)}
          />

          <Menu
            className="dropdown-menu dropdown-menu-full"
            open={this.state.menuOpened}
            onClose={() => this.setState({
              menuOpened: false,
              currentSearch: ''
            })}
          >
            {this.props.fetching &&
              <div>
                loading
              </div>
            }

            {!this.props.fetching && this.props.empty &&
              <div>
                empty
              </div>
            }

            {!this.props.fetching && !this.props.empty && Object.keys(this.props.results)
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
                        target: 'workspaces' === resultType ? workspaceRoute(result) : ('resources' === resultType ? resourceRoute(result) : userRoute(result))
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
