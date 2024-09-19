import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {toKey} from '#/main/core/scaffolding/text'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {DataFilter} from '#/main/app/data/components/filter'

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

    if (!isEmpty(value)) {
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
      <search className="list-search search-full">
        {this.props.available.map(availableFilter =>
          <DataFilter
            id={this.props.id+'-'+toKey(availableFilter.name)}
            key={availableFilter.name}
            type={availableFilter.type}
            placeholder={availableFilter.label}
            options={availableFilter.options}
            disabled={this.props.disabled || this.isFilterLocked(availableFilter.alias || availableFilter.name)}
            value={this.getFilterValue(availableFilter.alias || availableFilter.name)}
            updateSearch={(search) => this.updateFilter(availableFilter.alias ? availableFilter.alias : availableFilter.name, search)}
          />
        )}

        <Button
          className="btn btn-primary btn-search"
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
      </search>
    )
  }
}

SearchFull.propTypes = {
  id: T.string.isRequired,
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
