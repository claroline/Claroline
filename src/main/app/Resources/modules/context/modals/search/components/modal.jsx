import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

import {getApps} from '#/main/app/plugins'
import {SearchResults} from '#/main/app/context/modals/search/components/results'
import {SearchRecent} from '#/main/app/context/modals/search/components/recent'
import {route} from '#/main/core/tool/routing'

class SearchModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentSearch: '',
      availableSearches: {} // provided by plugins
    }

    this.updateSearch = this.updateSearch.bind(this)
    this.reset = this.reset.bind(this)
  }

  componentDidMount() {
    const searchApps = getApps('search')

    Promise
      .all(Object.keys(searchApps).map(type => searchApps[type]()))
      .then(searches => this.setState({
        availableSearches: searches.reduce((acc, current) => Object.assign({}, acc, {
          [current.default.name]: current.default
        }), {})
      }))
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
    return (
      <Modal
        {...omit(this.props, 'fetching', 'results', 'empty', 'search')}
        /*size="sm"*/
      >
        <div className="modal-body" role="presentation">
          <div className="app-search" role="form">
            <span className="app-search-icon fa fa-search" aria-hidden={true} />

            <input
              type="search"
              className="form-control form-control-lg"
              placeholder={trans('search', {}, 'actions')}
              value={this.state.currentSearch}
              autoFocus={true}
              onChange={(e) => this.updateSearch(e.target.value)}
            />

            {this.props.fetching &&
              <span className="app-search-loader">
                <span className="fa fa-circle-notch fa-spin"/>
              </span>
            }

            {!this.props.fetching && !isEmpty(this.state.currentSearch) &&
              <Button
                className="app-search-clear btn btn-text-secondary"
                type={CALLBACK_BUTTON}
                icon="fa fa-times"
                label={trans('close', {}, 'actions')}
                tooltip="bottom"
                callback={this.reset}
              />
            }
          </div>

          {!this.props.fetching && this.state.currentSearch &&
            <SearchResults
              empty={this.props.empty}
              results={this.props.results}
              availableSearches={this.state.availableSearches}
              reset={this.reset}
              fadeModal={this.props.fadeModal}
            />
          }

          {!this.state.currentSearch &&
            <SearchRecent
              fadeModal={this.props.fadeModal}
            />
          }
        </div>

        <div className="modal-footer gap-2 flex-column flex-md-row">
          Vous ne trouvez pas ce que vous cherchez ?

          <Button
            className="btn btn-primary"
            type={LINK_BUTTON}
            label={trans('Parcourir tous mes espaces', {}, 'history')}
            size="sm"
            target={route('workspaces')}
            onClick={this.props.fadeModal}
          />
        </div>
      </Modal>
    )
  }
}

SearchModal.propTypes = {
  fetching: T.bool.isRequired,
  results: T.object,
  empty: T.bool.isRequired,
  search: T.func.isRequired,

  fadeModal: T.func.isRequired
}

export {
  SearchModal
}
