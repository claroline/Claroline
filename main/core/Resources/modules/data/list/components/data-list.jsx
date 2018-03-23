import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import isEqual from 'lodash/isEqual'
import merge from 'lodash/merge'

import {trans, transChoice} from '#/main/core/translation'

import {constants as listConst} from '#/main/core/data/list/constants'
import {
  DataListAction,
  DataListProperty,
  DataListSelection,
  DataListSearch,
  DataListPagination
} from '#/main/core/data/list/prop-types'
import {
  createListDefinition,
  getDisplayableProps,
  getDisplayedProps,
  getFilterableProps
} from '#/main/core/data/list/utils'

import {ListEmpty} from '#/main/core/data/list/components/empty.jsx'
import {ListHeader} from '#/main/core/data/list/components/header.jsx'
import {ListFooter} from '#/main/core/data/list/components/footer.jsx'

/**
 * Full data list with configured components (eg. search, pagination).
 */
class DataList extends Component {
  constructor(props) {
    super(props)

    // processes list display configuration
    const definition = createListDefinition(this.props.definition)
    const currentDisplay = this.computeDisplay(
      this.props.display && this.props.display.current ? this.props.display.current : listConst.DEFAULT_DISPLAY_MODE,
      definition
    )

    // adds missing default in the definition
    this.state = Object.assign({}, currentDisplay, {
      definition: definition
    })

    // fills missing translations with default ones
    this.translations = merge({}, listConst.DEFAULT_TRANSLATIONS, this.props.translations)
  }

  componentWillReceiveProps(nextProps) {
    if (!isEqual(this.props.definition, nextProps.definition)
      || isEqual(this.props.display, nextProps.display)) {
      const definition = createListDefinition(nextProps.definition)
      const currentDisplay = this.computeDisplay(
        nextProps.display && nextProps.display.current ? nextProps.display.current : listConst.DEFAULT_DISPLAY_MODE,
        definition
      )

      this.setState(Object.assign({}, currentDisplay, {
        definition: createListDefinition(nextProps.definition)
      }))
    }
  }

  /**
   * Computes the list display info.
   *
   * @param {string}  displayMode - the new display mode
   * @param {array}   definition  - the list definition
   */
  computeDisplay(displayMode, definition) {
    let currentColumns
    if (listConst.DISPLAY_MODES[displayMode].filterColumns) {
      // gets only the displayed columns
      currentColumns = getDisplayedProps(definition)
    } else {
      // gets all displayable columns
      currentColumns = getDisplayableProps(definition)
    }

    return {
      currentColumns: currentColumns.map(prop => prop.name),
      currentDisplay: displayMode
    }
  }

  /**
   * Displays/Hides a data property in display modes that support it.
   *
   * @param {string} column - the name of the column to toggle
   */
  toggleColumn(column) {
    // Display/Hide columns is only available for display modes that support it (aka tables)
    if (listConst.DISPLAY_MODES[this.state.currentDisplay].filterColumns) {
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

  toggleDisplay(displayMode) {
    this.setState(
      this.computeDisplay(displayMode, this.state.definition)
    )
  }

  render() {
    // enables and configures list tools
    const availableDisplays = this.props.display.available ? this.props.display.available : Object.keys(listConst.DISPLAY_MODES)
    let displayTool
    if (1 < availableDisplays.length) {
      displayTool = {
        current: this.state.currentDisplay,
        available: availableDisplays,
        onChange: this.toggleDisplay.bind(this)
      }
    }

    let columnsTool
    if (this.props.filterColumns && listConst.DISPLAY_MODES[this.state.currentDisplay].filterColumns) {
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
        available: getFilterableProps(this.state.definition)
      })
    }

    // calculate actions
    let actions = this.props.actions.slice(0)
    if (this.props.deleteAction) {
      actions.push({
        icon: 'fa fa-fw fa-trash-o',
        label: trans('delete'),
        dangerous: true,
        displayed: this.props.deleteAction.displayed,
        disabled: this.props.deleteAction.disabled,
        action: typeof this.props.deleteAction.action === 'function' ?
          (rows) => this.props.deleteAction.action(
            rows,
            trans(this.translations.keys.deleteConfirmTitle, {}, this.translations.domain),
            transChoice(this.translations.keys.deleteConfirmQuestion, rows.length, {count: rows.length}, this.translations.domain)
          ) :
          this.props.deleteAction.action
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

        {0 < this.props.totalResults &&
          React.createElement(listConst.DISPLAY_MODES[this.state.currentDisplay].component, {
            size:          listConst.DISPLAY_MODES[this.state.currentDisplay].size,
            data:          this.props.data,
            count:         this.props.totalResults,
            columns:       this.state.definition.filter(prop => -1 !== this.state.currentColumns.indexOf(prop.name)),
            sorting:       this.props.sorting,
            selection:     this.props.selection,
            primaryAction: this.props.primaryAction,
            actions:       actions,
            card:          this.props.card
          })
        }

        {0 < this.props.totalResults &&
          <ListFooter totalResults={this.props.totalResults} pagination={this.props.pagination} />
        }

        {0 === this.props.totalResults &&
          <ListEmpty hasFilters={this.props.filters && 0 < this.props.filters.current.length} />
        }
      </div>
    )
  }
}

DataList.propTypes = {
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
   * Actions available for each data row and selected rows (if selection is enabled).
   */
  actions: T.arrayOf(
    T.shape(DataListAction.propTypes)
  ),

  /**
   * Data primary action (aka open/edit action for rows in most cases).
   * Providing this object will automatically display the primary action (depending on the current view mode).
   */
  primaryAction: T.shape({
    disabled: T.func,
    action: T.oneOfType([T.string, T.func]).isRequired
  }),

  /**
   * Data delete action.
   * Providing this object will automatically append the delete action to the actions list of rows and selection.
   */
  deleteAction: T.shape({
    disabled: T.func,
    displayed: T.func,
    // if a function is provided, it receive the `rows`, `confirmTitle`, `confirmQuestion` as param
    action: T.oneOfType([T.string, T.func]).isRequired
  }),

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
    ).isRequired,

    /**
     * Current format.
     */
    current: T.oneOf(Object.keys(listConst.DISPLAY_MODES)).isRequired
  }),

  /**
   * Filter displayed columns.
   * Setting it to true automatically enable the filter columns tool for supported displays.
   */
  filterColumns: T.bool,

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
   * A function to normalize data for card display.
   * - the data row is passed as argument
   * - the func MUST return an object respecting `DataCard.propTypes`.
   *
   * It's required to enable cards based display modes.
   */
  card: T.func.isRequired,

  /**
   * Override default list translations.
   */
  translations: T.shape({
    domain: T.string,
    keys: T.shape({
      searchPlaceholder: T.string,
      emptyPlaceholder: T.string,
      countResults: T.string,
      deleteConfirmTitle: T.string,
      deleteConfirmQuestion: T.string
    })
  })
}

DataList.defaultProps = {
  actions: [],
  filterColumns: true,
  display: {
    available: Object.keys(listConst.DISPLAY_MODES),
    current: listConst.DEFAULT_DISPLAY_MODE
  },
  translations: listConst.DEFAULT_TRANSLATIONS
}

export {
  DataList
}
