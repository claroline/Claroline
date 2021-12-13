import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import union from 'lodash/union'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

import {constants as searchConst} from '#/main/app/content/search/constants'
import {
  createListDefinition,
  getDisplayableProps,
  getFilterableProps,
  getSortableProps
} from '#/main/app/content/list/utils'
import {constants} from '#/main/app/content/list/constants'

import {DataListProperty as DataListPropertyTypes} from '#/main/app/content/list/prop-types'
import {ListParameters as ListParametersTypes} from '#/main/app/content/list/parameters/prop-types'

const isFilterable = (parameters) => parameters.filterable || !isEmpty(parameters.availableFilters)
const isSortable = (parameters) => parameters.sortable || !isEmpty(parameters.availableSort)
const isMultiDisplays = (parameters) => parameters.enableDisplays || !isEmpty(parameters.availableDisplays)
const isColumnsFilterable = (parameters) => parameters.columnsFilterable || !isEmpty(parameters.availableColumns)

const hasLargeCard = (parameters) => {
  const availableDisplays = get(parameters, 'availableDisplays') || []

  return (parameters.display && constants.DISPLAY_MODES[parameters.display].options.useCard && 'lg' === constants.DISPLAY_MODES[parameters.display].options.size)
    || !!availableDisplays
      .find(displayMode => constants.DISPLAY_MODES[displayMode].options.useCard && 'lg' === constants.DISPLAY_MODES[displayMode].options.size)
}

const ListForm = props => {
  const definition = createListDefinition(get(props.list, 'definition') || [])

  let displayModes
  if (get(props.list, 'display.available')) {
    // only grab available displays for the source
    displayModes = get(props.list, 'display.available')
  } else {
    // grab all implemented display modes
    displayModes = Object.keys(constants.DISPLAY_MODES)
  }

  if (!get(props.list, 'card')) {
    // the list implementation does not define cards, we need to disable cards based displays
    displayModes = displayModes.filter(displayName => !constants.DISPLAY_MODES[displayName].options.useCard)
  }

  const displayModesList = displayModes
    .reduce((acc, current) => Object.assign(acc, {[current]: constants.DISPLAY_MODES[current].label}), {})

  const pageSizesList = constants.AVAILABLE_PAGE_SIZES
    .reduce((pageChoices, current) => Object.assign(pageChoices, {[current]: -1 !== current ? current+'' : trans('all')}), {})

  const filtersList = getFilterableProps(definition)
    .reduce(
      (filterChoices, current) => Object.assign(filterChoices, {[current.alias || current.name]: current.label}), {}
    )

  const sortList = getSortableProps(definition)
    .reduce(
      (sortChoices, current) => Object.assign(sortChoices, {[current.alias || current.name]: current.label}), {}
    )

  const columnsList = getDisplayableProps(definition)
    .reduce(
      (columnChoices, current) => Object.assign(columnChoices, {[current.name]: current.label}), {}
    )

  return (
    <FormData
      embedded={true}
      level={props.level}
      name={props.name}
      dataPart={props.dataPart}
      sections={[
        {
          id: 'list-display',
          icon: 'fa fa-fw fa-layer-group',
          title: trans('list_display_modes'),
          fields: [
            {
              name: 'display',
              type: 'choice',
              label: trans('list_display_mode_default'),
              required: true,
              options: {
                choices: displayModesList,
                condensed: true
              },
              onChange: (value) => {
                if (value) {
                  if (isMultiDisplays(props.parameters)) {
                    props.updateProp('availableDisplays', union([value], get(props.parameters, 'availableDisplays') || []))
                  }

                  // Sets default columns list (all) for table
                  if (constants.DISPLAY_MODES[value].options.filterColumns
                    && (!props.parameters.columns || 0 === props.parameters.columns.length)) {
                    props.updateProp('columns', Object.keys(columnsList))
                  }
                }
              }
            }, {
              name: 'enableDisplays',
              type: 'boolean',
              label: trans('list_enable_display'),
              calculated: isMultiDisplays,
              onChange: (checked) => {
                if (checked) {
                  if (props.parameters.display) {
                    props.updateProp('availableDisplays', [props.parameters.display])
                  }
                } else {
                  props.updateProp('availableDisplays', [])
                }
              },
              linked: [
                {
                  name: 'availableDisplays',
                  type: 'choice',
                  label: trans('list_display_modes'),
                  required: true,
                  displayed: isMultiDisplays,
                  options: {
                    choices: displayModesList,
                    disabledChoices: props.parameters.display ? Object.keys(displayModesList).filter(displayMode => displayMode === props.parameters.display) : [],
                    noEmpty: true,
                    multiple: true,
                    inline: false
                  },
                  onChange: (selected) => {
                    if (-1 === selected.indexOf(props.parameters.display) && selected[0]) {
                      // the default display is no longer in the list of available, get the first available
                      props.updateProp('display', selected[0])
                    }

                    // Sets default columns list (all) for tables
                    if (selected.find(displayMode => constants.DISPLAY_MODES[displayMode].options.filterColumns)) {
                      props.updateProp('columns', Object.keys(columnsList))
                    }
                  }
                }
              ]
            }
          ]
        }, {
          id: 'list-filters',
          icon: 'fa fa-fw fa-search',
          title: trans('search_and_filters'),
          fields: [
            {
              name: 'filters',
              label: trans('list_filters_default'),
              type: 'collection',
              options: {
                type: 'filter',
                options: {
                  properties: getFilterableProps(definition)
                },
                placeholder: trans('no_filter'),
                button: trans('add_filter')
              },
              onChange: (filters) => {
                if (!isEmpty(filters) && isFilterable(props.parameters)) {
                  props.updateProp('availableFilters', union(filters.filter(filter => !!filter).map(filter => filter.property), props.parameters.availableFilters || []))
                }
              }
            }, {
              name: 'filterable',
              label: trans('list_enable_filters'),
              type: 'boolean',
              calculated: isFilterable,
              onChange: (filterable) => {
                if (filterable) {
                  if (!isEmpty(props.parameters.filters)) {
                    props.updateProp('availableFilters', props.parameters.filters.map(filter => filter.property))
                  }
                } else {
                  props.updateProp('availableFilters', [])
                }
              },
              linked: [
                {
                  name: 'searchMode',
                  label: trans('list_search_mode'),
                  type: 'choice',
                  required: true,
                  displayed: isFilterable,
                  options: {
                    condensed: true,
                    choices: searchConst.SEARCH_TYPES
                  }
                }, {
                  name: 'availableFilters',
                  label: trans('list_available_filters'),
                  type: 'choice',
                  required: true,
                  displayed: isFilterable,
                  options: {
                    choices: filtersList,

                    // removes locked filters from the list of available
                    disabledChoices: !isEmpty(props.parameters.filters) ?
                      Object.keys(filtersList).filter((availableFilter) => -1 !== props.parameters.filters.findIndex(filter => get(filter, 'property') === availableFilter))
                      : [],
                    multiple: true,
                    condensed: false,
                    inline: false
                  }
                }
              ]
            }
          ]
        }, {
          id: 'list-sorting',
          icon: 'fa fa-fw fa-sort-amount-down',
          title: trans('sorting'),
          fields: [
            {
              name: 'sorting',
              label: trans('list_sort_default'),
              type: 'choice',
              calculated: (parameters) => {
                if (parameters.sorting && 0 === parameters.sorting.indexOf('-')) {
                  return parameters.sorting.replace('-', '')
                }

                return parameters.sorting
              },
              onChange: (sorting) => {
                if (sorting && isSortable(props.parameters)) {
                  props.updateProp('availableSort', union([sorting], props.parameters.availableSort || []))
                }
              },
              options: {
                choices: getSortableProps(definition).reduce((sortChoices, current) => Object.assign(sortChoices, {
                  [current.alias || current.name]: current.label}
                ), {}),
                multiple: false,
                condensed: true
              },
              linked: [
                {
                  name: 'sortDirection',
                  type: 'choice',
                  hideLabel: true,
                  label: trans('list_sort_direction'),
                  displayed: (parameters) => !!parameters.sorting,
                  required: true,
                  calculated: (parameters) => {
                    if (parameters.sorting && 0 === parameters.sorting.indexOf('-')) {
                      return 'desc'
                    }

                    return 'asc'
                  },
                  options: {
                    choices: {
                      asc: trans('sort_asc'),
                      desc: trans('sort_desc')
                    }
                  },
                  onChange: (sortDirection) => {
                    let sorting
                    if ('asc' === sortDirection) {
                      sorting = props.parameters.sorting.replace('-', '')
                    } else {
                      sorting = '-'+props.parameters.sorting
                    }

                    props.updateProp('sorting', sorting)
                  }
                }
              ]
            }, {
              name: 'sortable',
              label: trans('list_enable_sorting'),
              type: 'boolean',
              calculated: isSortable,
              onChange: (sortable) => {
                if (sortable) {
                  if (props.parameters.sorting) {
                    const sortProp = 0 === props.parameters.sorting.indexOf('-') ? props.parameters.sorting.replace('-', '') : props.parameters.sorting

                    props.updateProp('availableSort', union([sortProp], props.parameters.availableSort || []))
                  }
                } else {
                  props.updateProp('availableSort', [])
                }
              },
              linked: [
                {
                  name: 'availableSort',
                  label: trans('list_available_sorts'),
                  type: 'choice',
                  displayed: isSortable,
                  required: true,
                  options: {
                    choices: sortList,
                    disabledChoices: Object
                      .keys(sortList)
                      .filter(sorting => {
                        if (props.parameters.sorting) {
                          const currentSort = 0 === props.parameters.sorting.indexOf('-') ? props.parameters.sorting.replace('-', '') : props.parameters.sorting

                          return sorting === currentSort
                        }

                        return false
                      }),
                    multiple: true,
                    condensed: false,
                    inline: false
                  }
                }
              ]
            }
          ]
        }, {
          id: 'list-results',
          icon: 'fa fa-fw fa-ellipsis-h',
          title: trans('results'),
          fields: [
            {
              name: 'actions',
              label: trans('list_display_actions'),
              type: 'boolean'
            }, {
              name: 'count',
              label: trans('list_display_count'),
              type: 'boolean'
            }, {
              name: 'paginated',
              label: trans('list_enable_pagination'),
              type: 'boolean',
              onChange: (enabled) => {
                if (!enabled) {
                  props.updateProp('pageSize', -1)
                  props.updateProp('availablePageSizes', [-1])
                }
              },
              linked: [
                {
                  name: 'pageSize',
                  label: trans('results_per_page'),
                  // show perf message if no pagination or default to all
                  help: !props.parameters.paginated || -1 === props.parameters.pageSize ? trans('list_all_results_perf_help') : undefined,
                  type: 'choice',
                  displayed: (parameters) => !!parameters.paginated,
                  required: true,
                  options: {
                    choices: pageSizesList,
                    condensed: true,
                    noEmpty: true
                  },
                  onChange: (value) => {
                    const availablePageSizes = get(props.parameters, 'availablePageSizes') || []
                    if (value && -1 === availablePageSizes.indexOf(value)) {
                      props.updateProp('availablePageSizes', [value].concat(availablePageSizes))
                    }
                  }
                }, {
                  name: 'availablePageSizes',
                  label: trans('list_available_page_sizes'),
                  type: 'choice',
                  displayed: (parameters) => !!parameters.paginated,
                  required: true,
                  options: {
                    choices: pageSizesList,
                    noEmpty: true,
                    multiple: true,
                    inline: false
                  },
                  onChange: (selected) => {
                    if (-1 === selected.indexOf(props.parameters.pageSize) && selected[0]) {
                      // the default page size is no longer in the list of available, get the first available
                      props.updateProp('pageSize', selected[0])
                    }
                  }
                }
              ]
            }
          ]
        }, {
          id: 'list-columns',
          icon: 'fa fa-fw fa-columns',
          title: trans('columns'),
          subtitle: trans('table_modes'),
          displayed: (parameters) => {
            const availableDisplays = get(parameters, 'availableDisplays') || []

            return (parameters.display && constants.DISPLAY_MODES[parameters.display].options.filterColumns)
              || !!availableDisplays.find(displayMode => constants.DISPLAY_MODES[displayMode].options.filterColumns)
          },
          fields: [
            {
              name: 'columns',
              label: trans('list_columns_default'),
              type: 'choice',
              required: true,
              options: {
                choices: columnsList,
                multiple: true,
                condensed: false,
                inline: false
              }
            }, {
              name: 'columnsFilterable',
              label: trans('list_enable_filter_columns'),
              type: 'boolean',
              calculated: isColumnsFilterable,
              onChange: (columnsFilterable) => {
                if (!columnsFilterable) {
                  props.updateProp('availableColumns', [])
                }
              },
              linked: [
                {
                  name: 'availableColumns',
                  label: trans('list_columns'),
                  type: 'choice',
                  displayed: isColumnsFilterable,
                  required: true,
                  options: {
                    choices: columnsList,
                    multiple: true,
                    condensed: false,
                    inline: false
                  }
                }
              ]
            }
          ]
        }, {
          id: 'list-card',
          icon: 'fa fa-fw fa-th-large',
          title: trans('cards'),
          subtitle: trans('grid_modes'),
          // display card configuration only if there is a grid view enabled
          displayed: (parameters) => {
            const availableDisplays = get(parameters, 'availableDisplays') || []

            return (parameters.display && constants.DISPLAY_MODES[parameters.display].options.useCard)
              || !!availableDisplays.find(displayMode => constants.DISPLAY_MODES[displayMode].options.useCard)
          },
          fields: [
            {
              name: 'card.display',
              label: trans('card_display_items'),
              type: 'choice',
              options: {
                condensed: false,
                inline: false,
                multiple: true,
                choices: merge({
                  icon: trans('card_icon'),
                  flags: trans('card_flags'),
                  subtitle: trans('card_subtitle')
                }, hasLargeCard(props.parameters) ? {
                  description: trans('card_description'),
                  footer: trans('card_footer')
                } : {})
              }
            }
          ]
        }
      ]}
    />
  )
}

ListForm.propTypes = {
  level: T.number,
  name: T.string.isRequired,
  dataPart: T.string,

  // the list we want to configure
  list: T.shape({
    definition: T.arrayOf(
      T.shape(DataListPropertyTypes.propTypes)
    ).isRequired,
    card: T.func,
    display: T.shape({
      current: T.string,
      available: T.arrayOf(T.string)
    })
  }).isRequired,

  parameters: T.shape(
    ListParametersTypes.propTypes
  ),

  // from redux
  updateProp: T.func.isRequired
}

ListForm.defaultProps = {
  parameters: {}
}

export {
  ListForm
}
