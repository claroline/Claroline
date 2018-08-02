import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {LineChart} from '#/main/core/layout/chart/line/components/line-chart'
import {constants as listConst} from '#/main/app/content/list/constants'

class LogList extends Component {
  
  componentWillReceiveProps(nextProps) {
    if (nextProps.chart.invalidated) {
      nextProps.getChartData(nextProps.id, nextProps.queryString)
    }
    
    this.setState(nextProps)
  }
  
  render() {
    let props =  this.props
    return (
      <div>
        { props.chart &&
        <div className="text-center">
          <LineChart
            data={props.chart.data}
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
          name="logs"
          fetch={{
            url: props.listUrl,
            autoload: true
          }}
          primaryAction={(row) =>({
            label: trans('date'),
            type: LINK_BUTTON,
            target: `/log/${row.id}`
          })}
          delete={false}
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
                choices: props.actions,
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
        
        />
      </div>
    )
  }
}

LogList.propTypes = {
  id: T.oneOfType([T.number, T.string]),
  listUrl: T.oneOfType([T.string, T.array]).isRequired,
  actions: T.array.isRequired,
  chart: T.object.isRequired,
  getChartData: T.func.isRequired,
  queryString: T.string
}

LogList.defaultProps = {
  id: null
}


export {
  LogList
}