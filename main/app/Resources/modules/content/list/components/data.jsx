import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import invariant from 'invariant'
import isEqual from 'lodash/isEqual'

import {constants as listConst} from '#/main/app/content/list/constants'
import {
  DataListProperty,
  DataListSelection,
  DataListSearch,
  DataListPagination
} from '#/main/app/content/list/prop-types'
import {
  createListDefinition,
  getDisplayableProps,
  getDisplayedProps,
  getFilterableProps
} from '#/main/app/content/list/utils'

import {ListEmpty} from '#/main/app/content/list/components/empty'
import {ListHeader} from '#/main/app/content/list/components/header'
import {ListFooter} from '#/main/app/content/list/components/footer'

/**
 * Full data list with configured components (eg. search, pagination).
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
  }

  componentWillReceiveProps(nextProps) {
    // display config or definition have changed
    if (!isEqual(this.props.definition, nextProps.definition)
      || !isEqual(this.props.display, nextProps.display)) {
      const definition = createListDefinition(nextProps.definition)
      const currentDisplay = this.computeDisplay(
        definition,
        isEqual(this.props.display, nextProps.display) ? this.state.display : nextProps.display,
        !!nextProps.card
      )

      this.setState(Object.assign({}, currentDisplay, {
        definition: createListDefinition(nextProps.definition)
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
    let availableDisplays = display.available ? display.available : Object.keys(listConst.DISPLAY_MODES)

    if (!hasCard) {
      // disables grid based displays if no card provided
      availableDisplays = availableDisplays.filter(displayName => !listConst.DISPLAY_MODES[displayName].options.useCard)

      // throws error if there is no available displays after filtering
      invariant(
        0 < availableDisplays.length,
        'Data list has no available displays. Either enable table displays or pass a DataCard component to the list.'
      )

      if (listConst.DISPLAY_MODES[currentDisplay].options.useCard) {
        // current display is a grid, change it
        currentDisplay = listConst.DISPLAY_MODES[listConst.DEFAULT_DISPLAY_MODE].options.useCard ?
          listConst.DEFAULT_DISPLAY_MODE : // gets the default mode if it's not card based
          // get the first non card based available display
          availableDisplays[0]
      }
    }

    let currentColumns
    if (listConst.DISPLAY_MODES[currentDisplay].options.filterColumns) {
      // gets only the displayed columns
      currentColumns = getDisplayedProps(definition)
    } else {
      // gets all displayable columns
      currentColumns = getDisplayableProps(definition)
    }

    return {
      display: {
        current: currentDisplay,
        available: availableDisplays
      },
      currentColumns: currentColumns.map(prop => prop.name)
    }
  }

  toggleDisplay(displayMode) {
    this.setState(
      this.computeDisplay(
        this.state.definition,
        { current: displayMode, available: this.state.display.available },
        !!this.props.card
      )
    )
  }

  /**
   * Displays/Hides a data property in display modes that support it.
   *
   * @param {string} column - the name of the column to toggle
   */
  toggleColumn(column) {
    // Display/Hide columns is only available for display modes that support it (aka tables)
    if (listConst.DISPLAY_MODES[this.state.display.current].options.filterColumns) {
      const newColumns = this.state.currentColumns.slice(0)

      // checks if the column is displayed
      const pos = newColumns.indexOf(column)
      if (-1 === pos) {
        // column is not displayed, display it
        newColumns.push(column)
      } else {
        // column is displayed, hide it
        newColumns.splice(pos, 1)
      }

      // updates displayed column list
      this.setState({currentColumns: newColumns})
    }
  }

  render() {
    // enables and configures list tools
    let displayTool
    if (1 < this.state.display.available.length) {
      displayTool = Object.assign({}, this.state.display, {
        onChange: this.toggleDisplay.bind(this)
      })
    }

    let columnsTool
    if (this.props.filterColumns && listConst.DISPLAY_MODES[this.state.display.current].options.filterColumns) {
      // Tools is enabled and the current display supports columns filtering
      const displayableColumns = getDisplayableProps(this.state.definition)
      if (1 < displayableColumns.length) {
        columnsTool = {
          current: this.state.currentColumns,
          available: getDisplayableProps(this.state.definition),
          toggle: this.toggleColumn.bind(this)
        }
      }
    }

    let filtersTool
    if (this.props.filters) {
      filtersTool = Object.assign({}, this.props.filters, {
        available: getFilterableProps(this.state.definition),
        readOnly: this.props.readOnly
      })
    }

    return (
      <div className="data-list">
        <ListHeader
          disabled={0 === this.props.totalResults}
          display={displayTool}
          columns={columnsTool}
          filters={filtersTool}
        />

        {0 !== this.props.totalResults &&
        React.createElement(listConst.DISPLAY_MODES[this.state.display.current].component, Object.assign({},
          listConst.DISPLAY_MODES[this.state.display.current].options,
          {
            data:          this.props.data,
            count:         this.props.totalResults,
            columns:       this.state.definition.filter(prop => -1 !== this.state.currentColumns.indexOf(prop.name)),
            sorting:       this.props.sorting,
            selection:     this.props.selection,
            primaryAction: this.props.primaryAction,
            actions:       this.props.actions,
            card:          this.props.card
          }
        ))
        }

        {0 !== this.props.totalResults &&
        <ListFooter totalResults={this.props.totalResults} pagination={this.props.pagination} />
        }

        {0 === this.props.totalResults &&
        <ListEmpty hasFilters={this.props.filters && 0 < this.props.filters.current.length} />
        }
      </div>
    )
  }
}

ListData.propTypes = {
  /**
   * The data list to display.
   */
  data: T.arrayOf(T.shape({
    // because some features (like selection) requires to retrieves some data rows
    id: T.oneOfType([T.string, T.number]).isRequired
  })).isRequired,

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
  primaryAction: T.func,

  /**
   * Actions available for each data row and selected rows (if selection is enabled).
   */
  actions: T.func,

  /**
   * Display formats of the list.
   * Providing this object automatically display the display formats component.
   */
  display: T.shape({
    /**
     * Available formats.
     */
    available: T.arrayOf(
      T.oneOf(Object.keys(listConst.DISPLAY_MODES))
    ),

    /**
     * Current format.
     */
    current: T.oneOf(Object.keys(listConst.DISPLAY_MODES))
  }),

  /**
   * Filter displayed columns.
   * Setting it to true automatically enable the filter columns tool for supported displays.
   */
  filterColumns: T.bool,

  /**
   * Is the filter in readonly mode.
   */
  readOnly: T.bool,

  /**
   * Search filters configuration.
   * Providing this object automatically display the search box component.
   */
  filters: T.shape(
    DataListSearch.propTypes
  ),

  /**
   * Sorting configuration.
   * Providing this object automatically display data sorting components.
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
   * Providing this object automatically display pagination and results per page components.
   */
  pagination: T.shape(
    DataListPagination.propTypes
  ),

  /**
   * Selection configuration.
   * Providing this object automatically display select checkboxes for each data results.
   */
  selection: T.shape(
    DataListSelection.propTypes
  ),

  /**
   * The card representation for the current data.
   * It's required to enable cards based display modes.
   *
   * It must be a react component.
   */
  card: T.func
}

ListData.defaultProps = {
  filterColumns: true,
  display: {
    available: Object.keys(listConst.DISPLAY_MODES),
    current: listConst.DEFAULT_DISPLAY_MODE
  }
}

export {
  ListData
}
