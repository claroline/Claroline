import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {ListForm} from '#/main/app/content/list/parameters/containers/form'
import {getSource} from '#/main/app/data'

import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/content/prop-types'

class ListWidgetParameters extends Component {
  constructor(props) {
    super(props)

    this.state = {
      parameters: undefined
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
      parameters: module.default.parameters
    }))
  }

  /*hasPerformanceWarn() {
    // lots of results
    return (!this.props.instance.parameters.maxResults || this.props.instance.parameters.maxResults > 100)
      // with no pagination or default pagination to all
      && (!this.props.instance.parameters.paginated || -1 === this.props.instance.parameters.pageSize)
  }*/

  render() {
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
              /*{
                name: 'maxResults',
                label: trans('list_total_results'),
                type: 'number',
                help: this.hasPerformanceWarn() ? trans('list_enable_pagination_perf_help') : undefined,
                options: {
                  placeholder: trans('all_results'),
                  min: 1
                }
              }*/
            ]
          }
        ]}
      >
        <ListForm
          level={5}
          name={this.props.name}
          dataPart="parameters"
          list={this.state.parameters}
          parameters={this.props.instance.parameters}
        />
      </FormData>
    )
  }
}

ListWidgetParameters.propTypes = {
  name: T.string.isRequired,
  instance: T.shape(
    WidgetInstanceTypes.propTypes
  ).isRequired
}

export {
  ListWidgetParameters
}
