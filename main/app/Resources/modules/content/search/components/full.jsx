import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {SearchProp} from '#/main/app/content/search/components/prop'

class SearchFull extends Component {
  constructor(props) {
    super(props)

    this.state = {
      updated: false,
      filters: props.current || []
    }
  }

  updateFilter(property, value) {
    let newFilters = [].concat(this.state.filters)
    const filterPos = newFilters.findIndex(filter => filter.property === property)

    if (undefined !== value && null !== value && (!value.hasOwnProperty('length') || 0 !== value.length)) {
      if (-1 !== filterPos) {
        newFilters[filterPos] = {
          property: property,
          value: value
        }
      } else {
        newFilters.push({
          property: property,
          value: value
        })
      }
    } else {
      if (-1 !== filterPos) {
        newFilters.splice(filterPos, 1)
      }
    }

    this.setState({
      updated: true,
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
      <div className="list-search search-full">
        {this.props.available.map(availableFilter =>
          <SearchProp
            key={availableFilter.name}
            type={availableFilter.type}
            placeholder={availableFilter.label}
            options={availableFilter.options}
            disabled={this.props.disabled || this.isFilterLocked(availableFilter.alias || availableFilter.name)}
            currentSearch={this.getFilterValue(availableFilter.alias || availableFilter.name)}
            updateSearch={(search) => this.updateFilter(availableFilter.alias ? availableFilter.alias : availableFilter.name, search)}
          />
        )}

        <Button
          className="btn btn-search"
          type={CALLBACK_BUTTON}
          icon="fa fa-search"
          label={trans('filter', {}, 'actions')}
          tooltip="bottom"
          callback={() => {
            this.props.resetFilters(this.state.filters)
            this.setState({updated: false})
          }}
          size="sm"
          disabled={this.props.disabled || !this.state.updated}
          primary={true}
        />
      </div>
    )
  }
}

SearchFull.propTypes = {
  disabled: T.bool,
  available: T.arrayOf(T.shape({ // TODO : use DataProp prop-types
    alias: T.string,
    name: T.string.isRequired,
    type: T.string.isRequired,
    options: T.object
  })).isRequired,
  current: T.arrayOf(T.shape({
    property: T.string.isRequired,
    value: T.any,
    locked: T.bool
  })).isRequired,
  resetFilters: T.func.isRequired
}

SearchFull.defaultProps = {
  disabled: false
}

export {
  SearchFull
}
