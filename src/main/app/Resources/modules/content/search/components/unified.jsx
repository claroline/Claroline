import React, {Component, forwardRef, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {toKey} from '#/main/core/scaffolding/text'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Menu, MenuOverlay} from '#/main/app/overlays/menu'

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
      <>
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

        <div className="row" role="presentation">
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

const SearchUnified = (props) => {
  const [currentText, updateText] = useState(props.currentText)
  const [opened, setOpened] = useState(false)
  const [updated, setUpdated] = useState(false)

  return (
    <form className="list-search search-unified flex-fill" role="search" action="#">
      <div className="d-flex align-items-center" role="presentation">
        <span className="search-icon fa fa-search text-secondary" aria-hidden={true} />

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
            className="btn btn-text-secondary px-2"
            type={CALLBACK_BUTTON}
            label={trans('search', {},'actions')}
            htmlType="submit"
            callback={() => {
              props.updateText(currentText)
              props.onSubmit()
            }}
          />
        }

        {((!isEmpty(props.current) || props.currentText) && props.currentText === currentText) &&
          <Button
            className="btn btn-text-secondary position-relative px-2"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-times"
            label={trans('remove_all_filter')}
            tooltip="bottom"
            callback={() => {
              updateText('')
              props.updateText('')
              props.resetFilters([])
            }}
          />
        }

        <Button
          className="btn btn-text-body dropdown-toggle search-btn position-relative px-2"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-filter"
          label={trans('filters')}
          tooltip="bottom"
          callback={() => setOpened(!opened)}
        >
          {!isEmpty(props.current) &&
            <span className="position-absolute end-0 bottom-0 translate-middle p-1 bg-danger rounded-circle" role="presentation">
              <span className="visually-hidden">New alerts</span>
            </span>
          }
        </Button>
      </div>

      <MenuOverlay
        id={`${props.id}-search-menu`}
        show={opened}
        onToggle={() => setOpened(false)}
      >
        <Menu
          align="end"
          as={SearchMenu}

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
      </MenuOverlay>
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
