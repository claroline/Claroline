import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {Await} from '#/main/app/components/await'
import {getType} from '#/main/app/data/types'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons/callback'
import {getPropDefinition} from '#/main/app/content/list/utils'

import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'

// TODO : reuse #/main/app/content/search/components/prop

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
  <TooltipOverlay
    id={props.id}
    tip={trans('list_search_invalid_filter')}
    position="right"
  >
    <span className="cursor-help fa fa-fw fa-warning" />
  </TooltipOverlay>

AvailableFilterFlag.propTypes = {
  id: T.string.isRequired,
  isValid: T.bool.isRequired
}

const AvailableFilterContent = props => {
  const isValidSearch = !isEmpty(props.currentSearch) && (!props.definition.validate || !props.definition.validate(props.currentSearch, props.options))

  return (
    <li role="presentation">
      {React.createElement(
        isValidSearch ? AvailableFilterActive : AvailableFilterDisabled,
        isValidSearch ? {onSelect: () => props.onSelect(props.definition.parse(props.currentSearch, props.options))} : {}, [
          <span key="available-filter-prop" className="available-filter-prop">
            <AvailableFilterFlag id={`${props.name}-filter-flag`} isValid={isValidSearch} />
            {props.label} <small>({props.type})</small>
          </span>,
          <span key="available-filter-form" className="available-filter-form">
            {!props.definition.components.search &&
              <span className="available-filter-value">{isValidSearch ? props.currentSearch : '-'}</span>
            }

            {props.definition.components.search &&
              React.createElement(props.definition.components.search, merge({}, props.options, {
                search: props.currentSearch,
                isValid: isValidSearch,
                updateSearch: props.onSelect
              }))
            }
          </span>
        ]
      )}
    </li>
  )
}

AvailableFilterContent.propTypes = {
  name: T.string.isRequired,
  label: T.string.isRequired,
  type: T.string.isRequired,
  currentSearch: T.string,
  onSelect: T.func.isRequired,
  options: T.object,
  definition: T.shape({
    // DataType
    parse: T.func.isRequired,
    validate: T.func.isRequired,
    components: T.shape({
      search: T.any // todo : find correct typing
    })
  }).isRequired
}

const AvailableFilter = (props) =>
  <Await
    for={getType(props.type)}
    then={(definition) => (
      <AvailableFilterContent {...props} definition={definition} />
    )}
  />

AvailableFilter.propTypes = {
  name: T.string.isRequired,
  label: T.string.isRequired,
  type: T.string.isRequired,
  currentSearch: T.string,
  onSelect: T.func.isRequired,
  options: T.object
}

AvailableFilter.defaultProps = {
  options: {}
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
class SearchUnified extends Component {
  constructor(props) {
    super(props)

    this.state = {
      opened: false,
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
    this.setState({currentSearch: search, opened: !isEmpty(search)})
  }

  render() {
    return (
      <div className={classes('list-search search-unified', {
        open: this.state.opened
      })}>
        <Button
          className="btn btn-link search-icon"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-search"
          label={trans('filters')}
          tooltip="bottom"
          callback={() => this.setState({opened : !this.state.opened})}
        />

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
            ref={(input) => this.searchInput = input}
            type="text"
            className="form-control search-control"
            placeholder={trans('list_search_placeholder')}
            value={this.state.currentSearch}
            disabled={this.props.disabled}
            onChange={(e) => this.updateSearch(e.target.value)}
          />
        </div>

        {this.state.opened &&
          <FiltersList
            available={this.props.available.filter(availableFilter =>
              // removes locked filters
              -1 === this.props.current.findIndex(currentFilter => (currentFilter.property === availableFilter.name || currentFilter.property === availableFilter.alias) && currentFilter.locked)
            )}
            currentSearch={this.state.currentSearch}
            onSelect={this.addFilter}
          />
        }
      </div>
    )
  }
}

SearchUnified.propTypes = {
  disabled: T.bool,
  available: T.arrayOf(T.shape({
    name: T.string.isRequired,
    options: T.object
  })).isRequired,
  current: T.arrayOf(T.shape({
    property: T.string.isRequired,
    value: T.any,
    locked: T.bool
  })).isRequired,
  addFilter: T.func.isRequired,
  removeFilter: T.func.isRequired
}

SearchUnified.defaultProps = {
  disabled: false
}

export {
  SearchUnified
}
