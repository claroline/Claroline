import React, {Component, forwardRef} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'
import cloneDeep from 'lodash/cloneDeep'

import {trans} from '#/main/app/intl/translation'
import {Await} from '#/main/app/components/await'
import {toKey} from '#/main/core/scaffolding/text'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {getType} from '#/main/app/data/types'
import {Menu, MenuOverlay} from '#/main/app/overlays/menu'

import {getPropDefinition} from '#/main/app/content/list/utils'
import {DataFilter} from '#/main/app/data/components/filter'

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

    console.log('mount')
    this.state = {
      updated: false,
      filters: props.current || []
    }
  }

  componentDidUpdate(prevProps) {
    if (!this.state.updated && (!isEmpty(prevProps.current || !isEmpty(this.props.current))) && prevProps.current !== this.props.current) {
      console.log('fuck')
      this.setState({filters: this.props.current})
    }
  }

  updateFilter(property, value, autoSubmit) {
    let newFilters = [].concat(this.state.filters)
    const filterPos = newFilters.findIndex(filter => filter.property === property)

    let updated = false
    if (undefined !== value && null !== value && (!Array.isArray(value) || 0 !== value.length)) {
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

    if (autoSubmit) {
      this.props.updateSearch(newFilters)

      this.setState({
        updated: false
      }, () => this.props.updateSearch(newFilters))
    } else {
      console.log(newFilters)
      this.setState({
        updated: updated,
        filters: newFilters
      }, () => this.props.updateFilters(newFilters))
    }
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
    console.log(this.state.filters)
    return (
      <>
        {this.props.available.map(filter =>
          <div key={filter.name} className="form-group row">
            <label className="col-sm-3 col-form-label col-form-label-sm text-end" htmlFor={this.props.id+'-'+toKey(filter.name)}>
              {filter.label}
            </label>

            <div className="col-sm-9">
              <DataFilter
                {...omit(filter)}

                id={this.props.id+'-'+toKey(filter.name)}
                size="sm"
                disabled={this.isFilterLocked(filter.alias || filter.name)}
                value={this.getFilterValue(filter.alias || filter.name)}
                updateSearch={(search, autoSubmit) => this.updateFilter(filter.alias ? filter.alias : filter.name, search, autoSubmit)}
              />
            </div>
          </div>
        )}

        <div className="row">
          <Button
            className="search-submit w-100"
            type={CALLBACK_BUTTON}
            variant="btn"
            htmlType="submit"
            size="lg"
            label={trans('search', {}, 'actions')}
            disabled={!this.props.updated}
            callback={() => {
              this.props.updateSearch(this.state.filters)
              //this.setState({updated: false})
            }}
            primary={true}
          />
        </div>
      </>
    )
  }
}

SearchForm.propTypes = {
  id: T.string.isRequired,
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
  updateSearch: T.func.isRequired,
  updateFilters: T.func.isRequired
}

const SearchMenu = forwardRef((props, ref) =>
  <div
    {...omit(props, 'updated', 'available', 'current', 'updateSearch', 'updateFilters', 'show', 'close')}
    className={classes('search-form dropdown-menu-full', props.className)}
    ref={ref}
  >
    <SearchForm {...props} />
  </div>
)

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
      updated: 0 !== search.length,
      opened: 0 !== search.length
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
      <form className="list-search search-unified" action="#">
        <div className="search-current" role="presentation">
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
              className="form-control form-control-lg search-control"
              placeholder={trans('list_search_placeholder')}
              value={this.state.currentSearch}
              disabled={this.props.disabled}
              onChange={(e) => this.updateSearch(e.target.value)}
            />
          </div>

          <Button
            className="btn btn-text-secondary dropdown-toggle search-btn"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-caret-down"
            label={trans('filters')}
            tooltip="bottom"
            callback={() => this.setState({opened : !this.state.opened})}
            disabled={this.props.disabled}
          />
        </div>

        <MenuOverlay
          id={`${this.props.id}-search-menu`}
          show={this.state.opened}
          onToggle={() => this.setState({opened: false})}
        >
          <Menu
            align="end"
            as={SearchMenu}

            id={this.props.id}
            updated={this.state.updated}
            current={this.getFormFilters()}
            available={this.props.available}
            updateFilters={() => this.setState({opened: true, updated: true})}
            updateSearch={(filters) => {
              this.props.resetFilters(filters)
              this.setState({currentSearch: '', updated: false, opened: false})
            }}
          />
        </MenuOverlay>
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
