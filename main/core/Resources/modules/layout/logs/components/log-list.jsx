import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {LineChart} from '#/main/core/layout/chart/line/components/line-chart'
import {constants as listConst} from '#/main/app/content/list/constants'

class LogList extends Component {
  componentDidMount() {
    if (this.props.chart.invalidated) {
      this.props.getChartData(this.props.id, this.props.queryString)
    }
  }

  componentDidUpdate(prevProps) {
    if (prevProps.chart.invalidated !== this.props.chart.invalidated && this.props.chart.invalidated) {
      this.props.getChartData(this.props.id, this.props.queryString)
    }
  }
  
  render() {
    return (
      <Fragment>
        {this.props.chart &&
          <div className="text-center">
            <LineChart
              data={this.props.chart.data}
              xAxisLabel={{
                show: true,
                text: trans('date'),
                grid: true
              }}
              yAxisLabel={{
                show: true,
                text: trans('actions'),
                grid: true
              }}
              height={250}
              width={700}
              showArea={true}
              margin={{
                top: 20,
                bottom: 50,
                left: 50,
                right: 20
              }}
            />
          </div>
        }
        <ListData
          name={this.props.name}
          fetch={{
            url: this.props.listUrl,
            autoload: true
          }}
          primaryAction={(row) =>({
            label: trans('date'),
            type: LINK_BUTTON,
            target: `${this.props.path}/log/${row.id}`
          })}
          definition={[
            {
              name: 'dateLog',
              type: 'date',
              label: trans('date'),
              displayed: true,
              primary: true,
              options: {
                time: true
              }
            }, {
              name: 'action',
              type: 'enum-plus',
              label: trans('action'),
              displayed: true,
              options: {
                choices: this.props.actions,
                transDomain: 'log'
              }
            }, {
              name: 'doer.name',
              type: 'string',
              label: trans('user'),
              displayed: true
            }, {
              name: 'description',
              type: 'html',
              label: trans('description'),
              displayed: true,
              filterable: false,
              sortable: false,
              options: {
                trust: true
              }
            }
          ]}
          
          display={{
            available : [listConst.DISPLAY_TABLE, listConst.DISPLAY_TABLE_SM],
            current: listConst.DISPLAY_TABLE
          }}
          selectable={false}
        />
      </Fragment>
    )
  }
}

LogList.propTypes = {
  id: T.oneOfType([T.number, T.string]),
  listUrl: T.oneOfType([T.string, T.array]).isRequired,
  actions: T.array.isRequired,
  chart: T.object.isRequired,
  getChartData: T.func.isRequired,
  queryString: T.string,
  name: T.string.isRequired,
  path: T.string
}

LogList.defaultProps = {
  id: null,
  name: 'logs',
  path: ''
}

export {
  LogList
}