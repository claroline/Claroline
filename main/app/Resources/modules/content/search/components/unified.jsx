import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'
import cloneDeep from 'lodash/cloneDeep'

import {trans} from '#/main/app/intl/translation'
import {Await} from '#/main/app/components/await'
import {toKey} from '#/main/core/scaffolding/text'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {getType} from '#/main/app/data/types'

import {getPropDefinition} from '#/main/app/content/list/utils'
import {SearchProp} from '#/main/app/content/search/components/prop'

const CurrentFilter = props =>
  <Await
    for={getType(props.type)}
    then={(definition) => (
      <div className="search-filter">
        <span className="search-filter-prop">
          {props.label}
        </span>

        <span className="search-filter-value">
          {definition.render(props.value, props.options)}

          {!props.locked &&
            <button type="button" className="btn btn-link" onClick={props.remove}>
              <span className="fa fa-times"/>
              <span className="sr-only">{trans('list_remove_filter')}</span>
            </button>
          }
        </span>
      </div>
    )}
  />

CurrentFilter.propTypes = {
  type: T.string.isRequired,
  label: T.string.isRequired,
  options: T.object,
  value: T.any,
  locked: T.bool,
  remove: T.func.isRequired
}

CurrentFilter.defaultProps = {
  options: {},
  locked: false
}

class SearchForm extends Component {
  constructor(props) {
    super(props)

    this.state = {
      updated: false,
      filters: props.current || []
    }
  }

  componentDidUpdate(prevProps) {
    if (prevProps.current !== this.props.current) {
      this.setState({filters: this.props.current})
    }
  }

  updateFilter(property, value) {
    let newFilters = [].concat(this.state.filters)
    const filterPos = newFilters.findIndex(filter => filter.property === property)

    let updated = false
    if (undefined !== value && null !== value && (!value.hasOwnProperty('length') || 0 !== value.length)) {
      if (-1 !== filterPos) {
        updated = value !== newFilters[filterPos].value
        newFilters[filterPos] = {
          property: property,
          value: value
        }
      } else {
        newFilters.push({
          property: property,
          value: value
        })
        updated = true
      }
    } else {
      if (-1 !== filterPos) {
        newFilters.splice(filterPos, 1)
        updated = true
      }
    }

    this.setState({
      updated: updated,
      filters: newFilters
    })
  }

  getFilterDefinition(property) {
    return this.state.filters.find(filterDef => property === filterDef.property)
  }

  isFilterLocked(property) {
    const filter = this.getFilterDefinition(property)
    if (filter) {
      return filter.locked || false
    }

    return false
  }

  getFilterValue(property) {
    const filter = this.getFilterDefinition(property)
    if (filter) {
      return filter.value
    }

    return null
  }

  render() {
    return (
      <div className="search-form dropdown-menu dropdown-menu-full">
        {false &&
          <ul className="nav nav-tabs">
            <li className="active">
              <a role="button" href="">Recherche avancée</a>
            </li>

            <li>
              <a role="button" href="">Mes recherches</a>
            </li>
          </ul>
        }

        {this.props.available.map(filter =>
          <div key={filter.name} className="form-group">
            <label className="control-label" htmlFor={toKey(filter.name)}>
              {filter.label}
            </label>

            <SearchProp
              id={toKey(filter.name)}

              {...omit(filter)}

              disabled={this.isFilterLocked(filter.alias || filter.name)}
              currentSearch={this.getFilterValue(filter.alias || filter.name)}
              updateSearch={(search) => this.updateFilter(filter.alias ? filter.alias : filter.name, search)}
            />
          </div>
        )}

        <div className="search-toolbar">
          {false &&
            <Button
              className="btn-link btn-emphasis"
              type={CALLBACK_BUTTON}
              label={trans('save', {}, 'actions')}
              disabled={!this.state.updated}
              callback={() => true}
            />
          }

          <Button
            className="btn btn-block btn-emphasis search-submit"
            type={CALLBACK_BUTTON}
            htmlType="submit"
            label={trans('search', {}, 'actions')}
            disabled={!this.state.updated && !this.props.updated}
            callback={() => {
              this.props.updateSearch(this.state.filters)
              this.setState({updated: false})
            }}
            primary={true}
          />
        </div>
      </div>
    )
  }
}

SearchForm.propTypes = {
  updated: T.bool,
  available: T.arrayOf(T.shape({
    name: T.string.isRequired,
    options: T.object
  })).isRequired,
  current: T.arrayOf(T.shape({
    property: T.string.isRequired,
    value: T.any,
    locked: T.bool
  })).isRequired,
  updateSearch: T.func.isRequired
}

/*<RootCloseWrapper
 disabled={this.props.disabled || !this.state.opened}
 event="click"
 onRootClose={() => this.setState({opened : !this.state.opened})}
 >*/

class SearchUnified extends Component {
  constructor(props) {
    super(props)

    this.state = {
      opened: false,
      currentSearch: '',
      updated: false
    }

    this.updateSearch = this.updateSearch.bind(this)
  }

  updateSearch(search) {
    this.setState({
      currentSearch: search,
      updated: 0 !== search.length
    })
  }

  getFormFilters() {
    const filters = cloneDeep(this.props.current)
    if (0 !== this.state.currentSearch.length) {
      // get the first available string filter which is not locked as default for now
      const defaultFilter = this.props.available.find(filter => 'string' === filter.type && -1 === filters.findIndex(value => filter.name === value.property && value.locked))
      if (defaultFilter) {
        const valuePos = filters.findIndex(value => defaultFilter.name === value.property)
        if (-1 !== valuePos) {
          // update existing value
          filters[valuePos].value = this.state.currentSearch
        } else {
          // push new filter
          filters.push({
            property: defaultFilter.alias || defaultFilter.name,
            value: this.state.currentSearch
          })
        }
      }
    }

    return filters
  }

  render() {
    return (
      <form
        className={classes('list-search search-unified dropdown', {
          open: this.state.opened || 0 !== this.state.currentSearch.length
        })}
        action="#"
      >
        <span className="search-icon fa fa-search" />

        <div className="search-filters">
          {this.props.current.map(activeFilter => {
            const propDef = getPropDefinition(activeFilter.property, this.props.available)

            return (
              <CurrentFilter
                key={`current-filter-${activeFilter.property}`}
                type={propDef.type}
                label={propDef.label}
                options={propDef.options}
                value={activeFilter.value}
                locked={activeFilter.locked}
                remove={() => this.props.removeFilter(activeFilter)}
              />
            )
          })}

          <input
            type="text"
            className="form-control search-control"
            placeholder={trans('list_search_placeholder')}
            value={this.state.currentSearch}
            disabled={this.props.disabled}
            onChange={(e) => this.updateSearch(e.target.value)}
          />
        </div>

        <Button
          className="btn btn-link dropdown-toggle search-btn"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-caret-down"
          label={trans('filters')}
          tooltip="bottom"
          callback={() => this.setState({opened : !this.state.opened})}
          disabled={this.props.disabled}
        />

        <SearchForm
          updated={this.state.updated}
          current={this.getFormFilters()}
          available={this.props.available}
          updateSearch={(filters) => {
            this.props.resetFilters(filters)
            this.setState({currentSearch: '', updated: false, opened: false})
          }}
        />
      </form>
    )
  }
}

SearchUnified.propTypes = {
  id: T.string.isRequired,
  disabled: T.bool,
  available: T.arrayOf(T.shape({
    name: T.string.isRequired,
    type: T.string.isRequired,
    options: T.object
  })).isRequired,
  current: T.arrayOf(T.shape({
    property: T.string.isRequired,
    value: T.any,
    locked: T.bool
  })).isRequired,
  addFilter: T.func.isRequired,
  removeFilter: T.func.isRequired,
  resetFilters: T.func.isRequired
}

SearchUnified.defaultProps = {
  disabled: false
}

export {
  SearchUnified
}
