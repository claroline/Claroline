import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/core/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions} from '#/main/app/content/form/store'

import {getSource} from '#/main/app/data'
import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/content/prop-types'

import {
  createListDefinition,
  getDisplayableProps,
  getFilterableProps,
  getSortableProps
} from '#/main/app/content/list/utils'
import {constants} from '#/main/app/content/list/constants'

class ListWidgetForm extends Component {
  constructor(props) {
    super(props)

    this.state = {
      definition: []
    }
  }

  componentDidMount() {
    this.loadSourceDefinition(this.props.instance.source)
  }

  componentWillReceiveProps(nextProps) {
    if (this.props.instance.source !== nextProps.instance.source) {
      this.loadSourceDefinition(nextProps.instance.source)
    }
  }

  loadSourceDefinition(source) {
    getSource(source).then(module => this.setState({
      definition: createListDefinition(module.default.parameters.definition)
    }))
  }

  hasTableModes() {
    const availableDisplays = get(this.props.instance, 'parameters.availableDisplays') || []

    return !!availableDisplays.find(displayMode => constants.DISPLAY_MODES[displayMode].options.filterColumns)
  }

  hasGridModes() {
    const availableDisplays = get(this.props.instance, 'parameters.availableDisplays') || []

    return !!availableDisplays.find(displayMode => constants.DISPLAY_MODES[displayMode].options.useCards)
  }

  hasPerformanceWarn() {
    // lots of results
    return (!this.props.instance.parameters.maxResults || this.props.instance.parameters.maxResults > 100)
      // with no pagination or default pagination to all
      && (!this.props.instance.parameters.paginated || -1 === this.props.instance.parameters.pageSize)
  }

  render() {
    let displayModes
    if (this.state.definition && get(this.state.definition, 'display.available')) {
      // only grab available displays for the source
      displayModes = get(this.state.definition, 'display.available')
    } else {
      // grab all implemented display modes
      displayModes = Object.keys(constants.DISPLAY_MODES)
    }

    const displayModesList = displayModes
      .reduce((acc, current) => Object.assign(acc, {[current]: constants.DISPLAY_MODES[current].label}), {})

    const pageSizesList = constants.AVAILABLE_PAGE_SIZES
      .reduce((pageChoices, current) => Object.assign(pageChoices, {[current]: -1 !== current ? current : trans('all')}), {})

    const columnsList = getDisplayableProps(this.state.definition)
      .reduce(
        (columnChoices, current) => Object.assign(columnChoices, {[current.name]: current.label}), {}
      )

    return (
      <FormData
        embedded={true}
        level={5}
        name={this.props.name}
        dataPart="parameters"
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'maxResults',
                label: trans('list_total_results'),
                type: 'number',
                help: this.hasPerformanceWarn() ? trans('list_enable_pagination_perf_help') : undefined,
                options: {
                  placeholder: trans('all_results'),
                  min: 1
                }
              }
            ]
          }, {
            icon: 'fa fa-fw fa-desktop',
            title: trans('display_parameters'),
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
                  const availableDisplays = get(this.props.instance, 'parameters.availableDisplays') || []
                  if (value && -1 === availableDisplays.indexOf(value)) {
                    this.props.updateProp(this.props.name, 'parameters.availableDisplays', [value].concat(availableDisplays))
                  }
                }
              }, {
                name: 'enableDisplays',
                type: 'boolean',
                label: trans('list_enable_display'),
                calculated: (parameters) => parameters.enableDisplays || (parameters.availableDisplays && parameters.availableDisplays.length > 1),
                onChange: (checked) => {
                  if (!checked) {
                    const currentDisplay = get(this.props.instance, 'parameters.display')
                    this.props.updateProp(this.props.name, 'parameters.availableDisplays', currentDisplay ? [currentDisplay] : [])
                  }
                },
                linked: [
                  {
                    name: 'availableDisplays',
                    type: 'choice',
                    label: trans('list_display_modes'),
                    required: true,
                    displayed: (parameters) => parameters.enableDisplays || (parameters.availableDisplays && parameters.availableDisplays.length > 1),
                    options: {
                      choices: displayModesList,
                      noEmpty: true,
                      multiple: true,
                      inline: false
                    },
                    onChange: (selected) => {
                      if (-1 === selected.indexOf(this.props.instance.parameters.display) && selected[0]) {
                        // the default display is no longer in the list of available, get the first available
                        this.props.updateProp(this.props.name, 'parameters.display', selected[0])
                      }
                    }
                  }
                ]
              }
            ]
          }, {
            icon: 'fa fa-fw fa-filter',
            title: trans('filters'),
            fields: [
              {
                name: 'filters',
                label: trans('list_filters_default'),
                type: 'collection',
                options: {
                  type: 'string',
                  options: {

                  },
                  placeholder: trans('no_filter'),
                  button: trans('add_filter')
                }
              }, {
                name: 'filterable',
                label: trans('list_enable_filters'),
                type: 'boolean',
                linked: [
                  {
                    name: 'availableFilters',
                    label: trans('list_available_filters'),
                    type: 'choice',
                    required: true,
                    displayed: (parameters) => !!parameters.filterable,
                    options: {
                      choices: getFilterableProps(this.state.definition)
                        .reduce(
                          (sortChoices, current) => Object.assign(sortChoices, {[current.name]: current.label}), {}
                        ),
                      multiple: true,
                      condensed: false,
                      inline: false
                    }
                  }
                ]
              }
            ]
          }, {
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
                options: {
                  choices: getSortableProps(this.state.definition)
                    .reduce(
                      (sortChoices, current) => Object.assign(sortChoices, {[current.name]: current.label}), {}
                    ),
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
                        sorting = this.props.instance.parameters.sorting.replace('-', '')
                      } else {
                        sorting = '-'+this.props.instance.parameters.sorting
                      }

                      this.props.updateProp(this.props.name, 'parameters.sorting', sorting)
                    }
                  }
                ]
              }, {
                name: 'sortable',
                label: trans('list_enable_sorting'),
                type: 'boolean',
                linked: [
                  {
                    name: 'availableSort',
                    label: trans('list_available_sorts'),
                    type: 'choice',
                    displayed: (parameters) => !!parameters.sortable,
                    required: true,
                    options: {
                      choices: getSortableProps(this.state.definition)
                        .reduce(
                          (sortChoices, current) => Object.assign(sortChoices, {[current.name]: current.label}), {}
                        ),
                      multiple: true,
                      condensed: false,
                      inline: false
                    }
                  }
                ]
              }
            ]
          }, {
            icon: 'fa fa-fw fa-ellipsis-h',
            title: trans('results'),
            fields: [
              {
                name: 'count',
                label: trans('list_display_count'),
                type: 'boolean'
              }, {
                name: 'paginated',
                label: trans('list_enable_pagination'),
                type: 'boolean',
                linked: [
                  {
                    name: 'pageSize',
                    label: trans('results_per_page'),
                    help: this.hasPerformanceWarn() ? trans('list_all_results_perf_help') : undefined,
                    type: 'choice',
                    displayed: (parameters) => !!parameters.paginated,
                    required: true,
                    options: {
                      choices: pageSizesList,
                      condensed: true,
                      noEmpty: true
                    },
                    onChange: (value) => {
                      const availablePageSizes = get(this.props.instance, 'parameters.availablePageSizes') || []
                      if (value && -1 === availablePageSizes.indexOf(value)) {
                        this.props.updateProp(this.props.name, 'parameters.availablePageSizes', [value].concat(availablePageSizes))
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
                      if (-1 === selected.indexOf(this.props.instance.parameters.pageSize) && selected[0]) {
                        // the default page size is no longer in the list of available, get the first available
                        this.props.updateProp(this.props.name, 'parameters.pageSize', selected[0])
                      }
                    }
                  }
                ]
              }
            ]
          }, {
            icon: 'fa fa-fw fa-columns',
            title: trans('columns'),
            subtitle: trans('table_modes'),
            displayed: this.hasTableModes(),
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
                onChange: (columnsFilterable) => {
                  if (!columnsFilterable) {
                    this.props.updateProp(this.props.name, 'parameters.availableColumns', [])
                  }
                },
                linked: [
                  {
                    name: 'availableColumns',
                    label: trans('list_columns'),
                    type: 'choice',
                    displayed: (parameters) => !!parameters.columnsFilterable || (parameters.availableColumns && 0 < parameters.availableColumns.length),
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
            icon: 'fa fa-fw fa-th-large',
            title: trans('cards'),
            subtitle: trans('grid_modes'),
            displayed: this.hasGridModes(),
            fields: [

            ]
          }
        ]}
      />
    )
  }
}

ListWidgetForm.propTypes = {
  name: T.string.isRequired,
  instance: T.shape(
    WidgetInstanceTypes.propTypes
  ).isRequired,

  // from redux
  updateProp: T.func.isRequired
}

const ListWidgetParameters = connect(
  null,
  (dispatch) => ({
    updateProp(formName, prop, value) {
      dispatch(actions.updateProp(formName, prop, value))
    }
  })
)(ListWidgetForm)

export {
  ListWidgetParameters
}
