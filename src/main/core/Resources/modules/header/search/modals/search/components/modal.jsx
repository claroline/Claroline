import React, {Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

import {getApps} from '#/main/app/plugins'

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
      >
        <div className="modal-body">
          <div className="app-search">
            <span className="app-search-icon fa fa-search" />

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

          {(this.state.currentSearch && this.props.empty) &&
            <div className="text-center mt-3">
              <p className="lead mb-1">{trans('no_search_results')}</p>
              <p className="mb-0 text-secondary">{trans('no_search_results_help')}</p>
            </div>
          }

          {this.state.currentSearch && !this.props.empty && Object.keys(this.props.results)
            .filter(resultType => !isEmpty(this.state.availableSearches[resultType]) && !isEmpty(this.props.results[resultType]))
            .map(resultType =>
              <div role="presentation" className="mt-3" key={resultType}>
                <h5>{this.state.availableSearches[resultType].label}</h5>
                <div className="data-cards-stacked">
                  {this.props.results[resultType].map(result =>
                    createElement(this.state.availableSearches[resultType].component, {
                      key: result.id,
                      size: 'xs',
                      direction: 'row',
                      data: result,
                      primaryAction: {
                        type: LINK_BUTTON,
                        label: trans('open', {}, 'actions'),
                        target: this.state.availableSearches[resultType].link(result),
                        onClick: this.reset
                      }
                    })
                  )}
                </div>
              </div>
            )
          }
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
