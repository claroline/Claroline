import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import invariant from 'invariant'
import classes from 'classnames'
import isEqual from 'lodash/isEqual'

import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentTitle} from '#/main/app/content/components/title'
import {Action as ActionTypes} from '#/main/app/action/prop-types'

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
  getFilterableProps,
  orderProps
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

    this.toggleDisplay = this.toggleDisplay.bind(this)
    this.changeColumns = this.changeColumns.bind(this)
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

  changeColumns(newColumns) {
    this.setState({currentColumns: newColumns})
  }

  render() {
    // enables and configures list tools
    let displayTool
    if (1 < this.state.display.available.length) {
      displayTool = Object.assign({}, this.state.display, {
        change: this.toggleDisplay
      })
    }

    let columnsTool
    if (listConst.DISPLAY_MODES[this.state.display.current].options.filterColumns) {
      // Tools is enabled and the current display supports columns filtering
      const displayableColumns = getDisplayableProps(this.state.definition)
      if (1 < displayableColumns.length) {
        columnsTool = {
          current: this.state.currentColumns,
          available: getDisplayableProps(this.state.definition),
          change: this.changeColumns
        }
      }
    }

    let filtersTool
    if (this.props.filters) {
      filtersTool = Object.assign({}, this.props.filters, {
        available: getFilterableProps(this.state.definition)
      })
    }

    return (
      <div className={classes('data-list', this.props.className)}>
        {this.props.title &&
          <ContentTitle
            level={this.props.level}
            displayLevel={this.props.displayLevel}
            title={this.props.title}
          />
        }

        {(displayTool || columnsTool || filtersTool || this.props.customActions) &&
          <ListHeader
            id={this.props.id}
            disabled={0 === this.props.totalResults}
            display={displayTool}
            columns={columnsTool}
            filters={filtersTool}
            customActions={this.props.customActions}
          />
        }

        {this.props.loading &&
          <ContentLoader />
        }

        {(!this.props.loading && 0 === this.props.totalResults) &&
          <ListEmpty hasFilters={this.props.filters && 0 < this.props.filters.current.length} />
        }

        {(!this.props.loading && 0 !== this.props.totalResults) &&
          React.createElement(listConst.DISPLAY_MODES[this.state.display.current].component, Object.assign({},
            listConst.DISPLAY_MODES[this.state.display.current].options,
            {
              data:          this.props.data,
              count:         this.props.totalResults,
              columns:       orderProps(this.state.definition.filter(prop => -1 !== this.state.currentColumns.indexOf(prop.name))),
              sorting:       this.props.sorting,
              selection:     this.props.selection,
              primaryAction: this.props.primaryAction,
              actions:       this.props.actions,
              card:          this.props.card
            }
          ))
        }

        {(!this.props.loading && 0 !== this.props.totalResults) && (this.props.count || this.props.pagination) &&
          <ListFooter
            count={this.props.count}
            totalResults={this.props.totalResults}
            pagination={this.props.pagination}
          />
        }
      </div>
    )
  }
}

ListData.propTypes = {
  id: T.string.isRequired,
  level: T.number,
  displayLevel: T.number,
  className: T.string,
  /**
   * @deprecated
   */
  title: T.string,
  loading: T.bool,

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
   * A list of actions to add to the list header.
   */
  customActions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),

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
   * Displays the list total results.
   */
  count: T.bool,

  /**
   * The card representation for the current data.
   * It's required to enable cards based display modes.
   *
   * It must be a react component.
   */
  card: T.func
}

ListData.defaultProps = {
  level: 2,
  loading: false,
  count: false,
  display: {
    available: Object.keys(listConst.DISPLAY_MODES),
    current: listConst.DEFAULT_DISPLAY_MODE
  }
}

export {
  ListData
}
