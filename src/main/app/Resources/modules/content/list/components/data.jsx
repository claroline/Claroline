import React, {createElement, Component} from 'react'
import {PropTypes as T} from 'prop-types'
import invariant from 'invariant'
import classes from 'classnames'
import isEqual from 'lodash/isEqual'

import {Action as ActionTypes} from '#/main/app/action/prop-types'

import {constants as listConst} from '#/main/app/content/list/constants'
import {
  DataListProperty,
  DataListDisplay,
  DataListSelection,
  DataListSearch,
  DataListPagination
} from '#/main/app/content/list/prop-types'
import {
  createListDefinition,
  getFilterableProps
} from '#/main/app/content/list/utils'

import DISPLAY_MODES from '#/main/app/content/list/modes'
import {ListEmpty} from '#/main/app/content/list/components/empty'
import {ListHeader} from '#/main/app/content/list/components/header'
import {ListFooter} from '#/main/app/content/list/components/footer'
import {ListError} from '#/main/app/content/list/components/error'

/**
 * Full data list with configured components (e.g., search, pagination).
 */
class ListData extends Component {
  constructor(props) {
    super(props)

    // processes list display configuration
    const definition = createListDefinition(this.props.definition)
    const currentDisplay = this.computeDisplay(definition, this.props.display, !!this.props.card)

    // adds missing default in the definition
    this.state = Object.assign({}, currentDisplay, {
      definition: definition
    })

    this.toggleDisplay = this.toggleDisplay.bind(this)
  }

  componentDidUpdate(prevProps) {
    // display config or definition have changed
    if (!isEqual(this.props.definition, prevProps.definition)
      || !isEqual(this.props.display, prevProps.display)
      || !isEqual(this.props.card, prevProps.card)
    ) {
      const definition = createListDefinition(this.props.definition)
      const currentDisplay = this.computeDisplay(definition, this.props.display, !!this.props.card)

      this.setState(Object.assign({}, currentDisplay, {
        definition: definition
      }))
    }
  }

  /**
   * Computes the list display info.
   *
   * @param {array}   definition - the list definition
   * @param {object}  display    - the new display mode
   * @param {boolean} hasCard
   */
  computeDisplay(definition, display = {}, hasCard = false) {
    let currentDisplay    = display.current ? display.current : listConst.DEFAULT_DISPLAY_MODE
    let availableDisplays = display.available ? display.available : listConst.DEFAULT_DISPLAY_MODES

    if (!hasCard) {
      // disables grid-based displays if no card provided
      availableDisplays = availableDisplays.filter(displayName => !DISPLAY_MODES[displayName].options.useCard)

      // throws error if there is no available display after filtering
      invariant(
        0 < availableDisplays.length,
        'Data list has no available displays. Either enable table displays or pass a DataCard component to the list.'
      )

      if (DISPLAY_MODES[currentDisplay].options.useCard) {
        // the current display is a grid, change it
        currentDisplay = DISPLAY_MODES[listConst.DEFAULT_DISPLAY_MODE].options.useCard ?
          // gets the default mode if it's not card-based
          listConst.DEFAULT_DISPLAY_MODE :
          // get the first non card-based available display
          availableDisplays[0]
      }
    }

    return {
      display: {
        current: currentDisplay,
        available: availableDisplays
      }
    }
  }

  toggleDisplay(displayMode) {
    this.setState(
      this.computeDisplay(
        this.state.definition,
        { current: displayMode, available: this.state.display.available },
        !!this.props.card
      ),
      () => {
        if (this.props.display && this.props.display.changeDisplay) {
          this.props.display.changeDisplay(displayMode)
        }
      }
    )
  }

  render() {
    // enables and configures list tools
    let displayTool
    if (1 < this.state.display.available.length) {
      displayTool = Object.assign({}, this.state.display, {
        changeDisplay: this.toggleDisplay
      })
    }

    let filtersTool
    if (this.props.filters) {
      filtersTool = Object.assign({}, this.props.filters, {
        available: getFilterableProps(this.state.definition)
      })
    }

    const empty = !this.props.loading && 0 === this.props.totalResults
    const hasFilters = this.props.filters && this.props.filters.current && 0 < this.props.filters.current.length

    return (
      <div
        className={classes('data-list', this.props.className, {'data-list-flush': this.props.flush})}
        role="presentation"
        aria-busy={this.props.loading}
      >
        {(displayTool || filtersTool || this.props.addAction) &&
          <ListHeader
            id={this.props.id}
            name={this.props.name}
            flush={this.props.flush}
            autoFocus={this.props.autoFocus}
            disabled={this.props.loading || 0 === this.props.totalResults}
            display={displayTool}
            filters={filtersTool}
            addAction={this.props.addAction}
          />
        }

        {!this.props.error && empty && !hasFilters && this.props.children}

        {!this.props.error && empty && (!this.props.children || hasFilters) &&
          <ListEmpty hasFilters={hasFilters} />
        }

        {(this.props.loading || 0 !== this.props.totalResults) &&
          createElement(DISPLAY_MODES[this.state.display.current].component, Object.assign({},
            DISPLAY_MODES[this.state.display.current].options,
            {
              data:          this.props.data,
              count:         this.props.totalResults,
              definition:    this.state.definition,
              sorting:       this.props.sorting,
              selection:     this.props.selection,
              primaryAction: this.props.primaryAction,
              actions:       this.props.actions,
              card:          this.props.card,
              loaded: this.props.loaded,
              invalidated: this.props.invalidated,
              flush: this.props.flush
            }
          ))
        }

        {this.props.error && !this.props.loading &&
          <ListError {...this.props.error} />
        }

        {0 !== this.props.totalResults && (this.props.count || this.props.pagination) &&
          <ListFooter
            count={this.props.count}
            totalResults={this.props.totalResults}
            pagination={this.props.pagination}
            disabled={this.props.loading}
          />
        }
      </div>
    )
  }
}

ListData.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  name: T.string.isRequired,
  flush: T.bool,
  autoFocus: T.bool,

  loading: T.bool,
  loaded: T.bool,
  invalidated: T.bool,

  /**
   * The data list to display.
   */
  data: T.arrayOf(T.shape({
    // because some features (like selection) require retrieving some data rows
    id: T.oneOfType([T.string, T.number]).isRequired
  })).isRequired,

  error: T.shape({
    code: T.string.isRequired,
    message: T.string,
    additional: T.any
  }),

  /**
   * Total results available in the list (without pagination if any).
   */
  totalResults: T.number.isRequired,

  /**
   * Definition of the data properties.
   */
  definition: T.arrayOf(
    T.shape(DataListProperty.propTypes)
  ).isRequired,

  /**
   * Data primary action (aka open/edit action for rows in most cases).
   * Providing this object will automatically display the primary action (depending on the current view mode).
   */
  primaryAction: T.oneOfType([
    T.string, // the name of an action available in props.actions
    T.func // an action generator which returns an action object
  ]),

  /**
   * Actions available for each data row and selected rows (if selection is enabled).
   */
  actions: T.func,

  /**
   * An action to add data in the list (displayed in the header if provided).
   */
  addAction: T.shape(
    ActionTypes.propTypes
  ),

  /**
   * Display formats of the list.
   * Providing this object automatically displays the display formats component.
   */
  display: T.shape(
    DataListDisplay.propTypes
  ),

  /**
   * Search filters configuration.
   * Providing this object automatically displays the search box component.
   */
  filters: T.shape(
    DataListSearch.propTypes
  ),

  /**
   * Sorting configuration.
   * Providing this object automatically displays data sorting components.
   */
  sorting: T.shape({
    current: T.shape({
      property: T.string,
      direction: T.number
    }).isRequired,
    updateSort: T.func.isRequired
  }),

  /**
   * Pagination configuration.
   * Providing this object automatically displays pagination and results per page components.
   */
  pagination: T.shape(
    DataListPagination.propTypes
  ),

  /**
   * Selection configuration.
   * Providing this object automatically displays select checkboxes for each data result.
   */
  selection: T.shape(
    DataListSelection.propTypes
  ),

  /**
   * Displays the list total results.
   */
  count: T.bool,

  /**
   * The card representation for the current data.
   * It's required to enable cards based display modes.
   *
   * It must be a React component.
   */
  card: T.func,

  children: T.func
}

ListData.defaultProps = {
  loading: false,
  invalidated: false,
  count: false,
  display: {
    available: listConst.DEFAULT_DISPLAY_MODES,
    current: listConst.DEFAULT_DISPLAY_MODE
  }
}

export {
  ListData
}
