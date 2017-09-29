import merge from 'lodash/merge'

import {DataProperty} from '#/main/core/layout/list/prop-types'

/**
 * Fills definition with missing default values.
 *
 * @param {Array} definition
 *
 * @return {Array} - the defaulted definition
 */
function createListDefinition(definition) {
  return definition.map(dataDef => merge({}, DataProperty.defaultProps, dataDef))
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
 * Gets available actions for each data object.
 *
 * @param {Array} actions - the whole set of available data actions
 *
 * @returns {Array}
 */
function getRowActions(actions = []) {
  return actions.filter(action => !action.context || 'row' === action.context)
}

/**
 * Gets available actions for selected data objects.
 *
 * @param {Array} actions - the whole set of available data actions
 *
 * @returns {Array}
 */
function getBulkActions(actions = []) {
  return actions.filter(action => !action.context || 'selection' === action.context)
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


/**
 * Counts the number of pages of the list.
 *
 * @param {number} totalResults
 * @param {number} pageSize
 *
 * @returns {number}
 */
function countPages(totalResults, pageSize) {
  if (-1 === pageSize) {
    return 1
  }

  const rest = totalResults % pageSize
  const nbPages = (totalResults - rest) / pageSize

  return nbPages + (rest > 0 ? 1 : 0)
}

function getDataQueryString(dataObjects) {
  return '?' + dataObjects.map(object => 'ids[]='+object.id).join('&')
}

export {
  createListDefinition,
  getPropDefinition,
  getRowActions,
  getBulkActions,
  getDisplayableProps,
  getDisplayedProps,
  getFilterableProps,
  getSortableProps,
  isRowSelected,
  countPages,
  getDataQueryString
}
