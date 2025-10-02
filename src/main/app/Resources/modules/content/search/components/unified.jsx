import React, {Component, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {toKey} from '#/main/app/utils/text'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'
import {Menu} from '#/main/app/overlays/menu'

import {DataFilter} from '#/main/app/data/components/filter'

class SearchForm extends Component {
  constructor(props) {
    super(props)

    this.state = {
      updated: false,
      filters: props.current || []
    }
  }

  componentDidUpdate(prevProps) {
    if (!this.state.updated && (!isEmpty(prevProps.current || !isEmpty(this.props.current))) && prevProps.current !== this.props.current) {
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
    return (
      <Menu
        align="end"
        className="search-form"
        style={{minWidth: '34rem'}}
      >
        {this.props.available.map(filter =>
          <div key={filter.name} className="form-group row" role="presentation">
            <label className="col-sm-3 col-form-label col-form-label-sm text-end" htmlFor={this.props.id+'-'+toKey(filter.name)}>
              {filter.label}
            </label>

            <div className="col-sm-9" role="presentation">
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

        <div className="row px-3 py-2 bg-body-tertiary d-flex rounded-bottom-1" role="presentation">
          <Button
            className="btn btn-primary ms-auto"
            type={CALLBACK_BUTTON}
            htmlType="submit"
            label={trans('search', {}, 'actions')}
            disabled={!this.props.updated}
            callback={() => {
              this.props.updateSearch(this.state.filters)
              //this.setState({updated: false})
            }}
            primary={true}
          />
        </div>
      </Menu>
    )
  }
}

SearchForm.propTypes = {
  id: T.string.isRequired,
  className: T.string,
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

const SearchUnified = (props) => {
  const [currentText, updateText] = useState(props.currentText)
  const [opened, setOpened] = useState(false)
  const [updated, setUpdated] = useState(false)

  const deletableFilters = (props.current || []).filter(filter => !filter.locked)

  return (
    <form className="list-search search-unified flex-fill" action="#">
      <div className="d-flex align-items-center" role="presentation">
        <span className="search-icon fa fa-search text-body-secondary" aria-hidden={true} />

        <input
          type="text"
          className="form-control form-control-lg search-control py-0 px-3"
          placeholder={trans('list_search_placeholder')}
          value={currentText}
          autoFocus={props.autoFocus}
          onChange={(e) => updateText(e.target.value)}
        />

        {(currentText !== props.currentText) &&
          <Button
            className="btn btn-text-body px-2 focus-ring focus-ring-secondary"
            type={CALLBACK_BUTTON}
            label={trans('search', {},'actions')}
            htmlType="submit"
            callback={() => {
              props.updateText(currentText)
              props.onSubmit()
            }}
          />
        }

        {((!isEmpty(deletableFilters) || props.currentText) && props.currentText === currentText) &&
          <Button
            className="btn btn-text-body position-relative px-2 focus-ring focus-ring-secondary"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-delete-left"
            label={trans('remove_filters', {}, 'actions')}
            tooltip="bottom"
            callback={() => {
              updateText('')
              props.updateText('')

              // only keeps locked filters
              props.resetFilters((props.current || []).filter(filter => filter.locked))
            }}
          />
        }

        <Button
          className="btn btn-text-body dropdown-toggle search-btn position-relative px-2 focus-ring focus-ring-secondary"
          type={MENU_BUTTON}
          icon="fa fa-fw fa-filter"
          label={trans('filters')}
          tooltip="bottom"
          opened={opened}
          onToggle={() => setOpened(!opened)}
          menu={
            <SearchForm
              id={props.id}
              updated={updated}
              current={props.current}
              available={props.available}
              updateFilters={() => {
                setOpened(true)
                setUpdated(true)
              }}
              updateSearch={(filters) => {
                props.resetFilters(filters)
                setOpened(false)
                setUpdated(false)
                //this.setState({currentSearch: '', updated: false, opened: false})
              }}
            />
          }
        >
          {!isEmpty(props.current) &&
            <span className="position-absolute end-0 bottom-0 translate-middle p-1 bg-danger rounded-circle" role="presentation">
              <span className="visually-hidden">New alerts</span>
            </span>
          }
        </Button>
      </div>
    </form>
  )
}

SearchUnified.propTypes = {
  id: T.string.isRequired,
  disabled: T.bool,
  autoFocus: T.bool,
  available: T.arrayOf(T.shape({
    name: T.string.isRequired,
    type: T.string.isRequired,
    options: T.object
  })).isRequired,

  // from store
  currentText: T.string,
  current: T.arrayOf(T.shape({
    property: T.string.isRequired,
    value: T.any,
    locked: T.bool
  })).isRequired,

  onSubmit: T.func,

  updateText: T.func.isRequired,
  addFilter: T.func.isRequired,
  removeFilter: T.func.isRequired,
  resetFilters: T.func.isRequired
}

SearchUnified.defaultProps = {
  disabled: false,
  autoFocus: false
}

export {
  SearchUnified
}
