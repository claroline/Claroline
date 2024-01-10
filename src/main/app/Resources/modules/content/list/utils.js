import merge from 'lodash/merge'

import {isTypeEnabled} from '#/main/app/data/types'

import {DataListProperty} from '#/main/app/content/list/prop-types'

/**
 * Fills definition with missing default values.
 *
 * @param {Array} definition
 *
 * @return {Array} - the defaulted definition
 */
function createListDefinition(definition) {
  return orderProps(definition
    // add default
    .map(dataDef => merge({}, DataListProperty.defaultProps, dataDef))
    // remove disabled types
    .filter(dataDef => isTypeEnabled(dataDef.type))
  )
}

/**
 * Retrieves the definition of a data property by its name or alias.
 *
 * @param {string} propName - the name or alias of the data property to find.
 * @param {Array} dataProps - the full definition of the rendered data list.
 *
 * @return {object} - the definition object of the data property.
 */
function getPropDefinition(propName, dataProps) {
  return dataProps.find(prop => (prop.alias && propName === prop.alias) || propName === prop.name)
}

/**
 * Gets primary action for each data object.
 *
 * @param {object}   item            - The current row data.
 * @param {function} actionGenerator - A function to generate the primary action for a data row.
 *
 * @returns {Array}
 */
function getPrimaryAction(item, actionGenerator) {
  if (actionGenerator) {
    return actionGenerator(item)
  }

  return null
}

/**
 * Gets available actions for selected data objects.
 *
 * @param {Array}    items            - The current item list.
 * @param {function} actionsGenerator - A function to generate the set of available actions for a data selection.
 *
 * @returns {Array}
 */
function getActions(items, actionsGenerator) {
  if (actionsGenerator) {
    // generates action
    return actionsGenerator(items)
  }

  return []
}

/**
 * Extracts displayable props from the data definition.
 *
 * @param {Array} dataProps - the full definition of the rendered data list.
 *
 * @return {Array} - the list of displayable data properties
 */
function getDisplayableProps(dataProps) {
  return dataProps.filter(prop => prop.displayable)
}

/**
 * Extracts default displayed props from the data definition.
 *
 * @param {Array} dataProps - the full definition of the rendered data list.
 *
 * @return {Array} - the list of default displayed data properties
 */
function getDisplayedProps(dataProps) {
  return dataProps.filter(prop => prop.displayed)
}

/**
 * Extracts filterable props from the data definition.
 *
 * @param {Array} dataProps - the full definition of the rendered data list.
 *
 * @return {Array} - the list of filterable data properties
 */
function getFilterableProps(dataProps) {
  return dataProps.filter(prop => prop.filterable)
}

/**
 * Extracts sortable props from the data definition.
 *
 * @param {Array} dataProps - the full definition of the rendered data list.
 *
 * @return {Array} - the list of sortable data properties
 */
function getSortableProps(dataProps) {
  return dataProps.filter(prop => prop.sortable)
}

/**
 * Checks whether a data object is part of the selection.
 *
 * @param {object} row       - the data object to search.
 * @param {Array}  selection - the list of current selected IDs.
 */
function isRowSelected(row, selection) {
  return selection && -1 !== selection.indexOf(row.id)
}

function orderProps(dataProps) {
  return dataProps.sort((a, b) => {
    if (undefined === a.order && undefined === b.order) {
      return 0
    } else if (undefined === a.order) {
      return 1
    } else if (undefined === b.order) {
      return -1
    }

    return a.order - b.order
  })
}

function parseSortBy(sortByStr) {
  if (!sortByStr) {
    return null
  }

  let reverse = sortByStr.startsWith('-')

  return {
    property: reverse ? sortByStr.substr(1) : sortByStr,
    direction: reverse ? -1 : 1
  }
}

export {
  createListDefinition,
  getPropDefinition,
  getPrimaryAction,
  getActions,
  getDisplayableProps,
  getDisplayedProps,
  getFilterableProps,
  getSortableProps,
  isRowSelected,
  parseSortBy
}
