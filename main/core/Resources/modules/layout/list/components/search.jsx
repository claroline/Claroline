import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {t} from '#/main/core/translation'
import {getTypeOrDefault} from '#/main/core/layout/data'
import {getPropDefinition} from '#/main/core/layout/list/utils'

import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'

const CurrentFilter = props => {
  const typeDef = getTypeOrDefault(props.type)

  return (
    <div className="search-filter">
      <span className="search-filter-prop">
        {props.label}
      </span>

        <span className="search-filter-value">
          {typeDef.render(props.value)}

          <button type="button" className="btn btn-link" onClick={props.remove}>
          <span className="fa fa-times" />
          <span className="sr-only">{t('list_remove_filter')}</span>
        </button>
      </span>
    </div>
  )
}

CurrentFilter.propTypes = {
  type: T.string.isRequired,
  label: T.string.isRequired,
  value: T.any.isRequired,
  remove: T.func.isRequired
}

const AvailableFilterActive = props =>
  <a
    className="available-filter available-filter-active"
    role="button"
    href=""
    onClick={(e) => {
      e.preventDefault()
      props.onSelect()
    }}
  >
    {props.children}
  </a>

AvailableFilterActive.propTypes = {
  children: T.node.isRequired,
  onSelect: T.func.isRequired
}

const AvailableFilterDisabled = props =>
  <span className="available-filter available-filter-disabled">
    {props.children}
  </span>

AvailableFilterDisabled.propTypes = {
  children: T.node.isRequired
}

const AvailableFilterFlag = props => props.isValid ?
  <span className="fa fa-fw" />
  :
  <TooltipElement
    id={props.id}
    tip={t('list_search_invalid_filter')}
    position="right"
  >
    <span className="cursor-help fa fa-fw fa-warning" />
  </TooltipElement>

AvailableFilterFlag.propTypes = {
  id: T.string.isRequired,
  isValid: T.bool.isRequired
}

const AvailableFilter = props => {
  const typeDef = getTypeOrDefault(props.type)
  const isValidSearch = !typeDef.validate || typeDef.validate(props.currentSearch)

  return (
    <li role="presentation">
      {React.createElement(
        isValidSearch ? AvailableFilterActive : AvailableFilterDisabled,
        isValidSearch ? {onSelect: () => props.onSelect(typeDef.parse(props.currentSearch))} : {}, [
          <span key="available-filter-prop" className="available-filter-prop">
            <AvailableFilterFlag id={`${props.name}-filter-flag`} isValid={isValidSearch} />
            {props.label} <small>({props.type})</small>
          </span>,
          <span key="available-filter-form" className="available-filter-form">
            {!typeDef.components.search &&
              <span className="available-filter-value">{isValidSearch ? props.currentSearch : '-'}</span>
            }

            {typeDef.components.search &&
              React.createElement(typeDef.components.search, {
                search: props.currentSearch,
                isValid: isValidSearch,
                updateSearch: props.onSelect,
                options: props.options
              })
            }
          </span>
        ]
      )}
    </li>
  )
}

AvailableFilter.propTypes = {
  name: T.string.isRequired,
  label: T.string.isRequired,
  type: T.string.isRequired,
  currentSearch: T.string,
  onSelect: T.func.isRequired,
  options: T.object
}

const FiltersList = props =>
  <menu className="search-available-filters">
    {props.available.map(filter =>
      <AvailableFilter
        key={`available-filter-${filter.name}`}
        name={filter.name}
        label={filter.label}
        type={filter.type}
        options={filter.options}
        currentSearch={props.currentSearch}
        onSelect={(filterValue) => props.onSelect(filter.alias ? filter.alias : filter.name, filterValue)}
      />
    )}
  </menu>

FiltersList.propTypes = {
  available: T.arrayOf(T.shape({
    name: T.string.isRequired,
    alias: T.string,
    type: T.string.isRequired,
    label: T.string.isRequired,
    options: T.object
  })).isRequired,
  currentSearch: T.string,
  onSelect: T.func.isRequired
}

FiltersList.defaultProps = {
  currentSearch: ''
}

/**
 * Data list search box.
 *
 * @param props
 * @constructor
 */
class ListSearch extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentSearch: ''
    }

    this.addFilter = this.addFilter.bind(this)
    this.updateSearch = this.updateSearch.bind(this)
  }

  componentDidMount() {
    this.searchInput.focus()
  }

  addFilter(filterName, filterValue) {
    // reset current search field
    this.updateSearch('')

    // focus again the search field to avoid clicks when adding multiple filters
    this.searchInput.focus()

    // update filters list
    this.props.addFilter(filterName, filterValue)
  }

  updateSearch(search) {
    this.setState({currentSearch: search})
  }

  render() {
    return (
      <div className={classes('list-search', {
        open: this.state.currentSearch
      })}>
        <div className="search-filters">
          {this.props.current.map(activeFilter => {
            const propDef = getPropDefinition(activeFilter.property, this.props.available)

            return (
              <CurrentFilter
                key={`current-filter-${activeFilter.property}`}
                type={propDef.type}
                label={propDef.label}
                value={activeFilter.value}
                remove={() => this.props.removeFilter(activeFilter)}
              />
            )
          })}

          <input
            ref={(input) => this.searchInput = input}
            type="text"
            className="form-control search-control"
            placeholder={t('list_search_placeholder')}
            value={this.state.currentSearch}
            disabled={this.props.disabled}
            onChange={(e) => this.updateSearch(e.target.value)}
          />
        </div>

        <span className="search-icon" aria-hidden="true" role="presentation">
          <span className="fa fa-fw fa-search" />
        </span>

        {this.state.currentSearch &&
          <FiltersList
            available={this.props.available}
            currentSearch={this.state.currentSearch}
            onSelect={this.addFilter}
          />
        }
      </div>
    )
  }
}

ListSearch.propTypes = {
  disabled: T.bool,
  available: T.arrayOf(T.shape({
    name: T.string.isRequired,
    options: T.object
  })).isRequired,
  current: T.arrayOf(T.shape({
    property: T.string.isRequired,
    value: T.any.isRequired
  })).isRequired,
  addFilter: T.func.isRequired,
  removeFilter: T.func.isRequired
}

ListSearch.defaultProps = {
  disabled: false
}

export {
  ListSearch
}
