import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {schemeCategory20c} from 'd3-scale'

import {trans, displayDuration} from '#/main/app/intl'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {LineChart} from '#/main/core/layout/chart/line/components/line-chart'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'

import {selectors} from '#/plugin/analytics/charts/connection-time/store/selectors'

class ConnectionTimeChart extends Component {
  constructor(props) {
    super(props)

    this.state = {
      range: 'week'
    }
  }

  componentDidMount() {
    if (!this.props.loaded) {
      this.props.fetchConnectionTime(this.props.url)
    }
  }

  changeRange(range) {
    this.setState({range: range})

    // TODO : add range
    this.props.fetchConnectionTime(this.props.url)
  }

  render() {
    return (
      <Fragment>
        <div className="panel panel-default panel-analytics">
          <div className="panel-heading">
            <h2 className="panel-title">
              {trans('connection_time')}
            </h2>

            {false && // TODO
              <Toolbar
                className="panel-actions"
                buttonName="btn-link"
                actions={[
                  {
                    name: 'week',
                    type: CALLBACK_BUTTON,
                    label: trans('range_week', {}, 'analytics'),
                    callback: () => this.changeRange('week'),
                    active: 'week' === this.state.range
                  }, {
                    name: 'month',
                    type: CALLBACK_BUTTON,
                    label: trans('range_month', {}, 'analytics'),
                    callback: () => this.changeRange('month'),
                    active: 'month' === this.state.range
                  }, {
                    name: 'year',
                    type: CALLBACK_BUTTON,
                    label: trans('range_year', {}, 'analytics'),
                    callback: () => this.changeRange('year'),
                    active: 'year' === this.state.range
                  }
                ]}
              />
            }
          </div>

          {false &&
            <div className="panel-body text-right" style={{paddingTop: '11px'}}>
              <Toolbar
                id="activity-legend"
                className="chart-legend"
                buttonName="btn-link"
                actions={[
                  {
                    name: 'actions',
                    type: CALLBACK_BUTTON,
                    icon: <span className="action-icon fa fa-fw fa-circle icon-with-text-right" style={{color: schemeCategory20c[0]}}/>,
                    label: trans('Temps total'),
                    callback: () => true
                  }, {
                    name: 'visitors',
                    type: CALLBACK_BUTTON,
                    icon: <span className="action-icon fa fa-fw fa-circle icon-with-text-right" style={{color: schemeCategory20c[4]}}/>,
                    label: trans('Temps moyen'),
                    callback: () => true
                  }
                ]}
              />

              <LineChart
                data={[
                  this.props.data.total,
                  this.props.data.average
                ]}
                xAxisLabel={{
                  show: false,
                  text: trans('date'),
                  grid: false
                }}
                yAxisLabel={{
                  show: false,
                  text: trans('actions'),
                  grid: true
                }}
                height={250}
                width={680}
                showArea={true}
                margin={{
                  left: 25,
                  top: 5,
                  right: 0,
                  bottom: 30
                }}
                colors={[schemeCategory20c[0], schemeCategory20c[4]]}
              />
            </div>
          }
        </div>

        <div className="panel panel-default embedded-list-section">
          <div className="panel-body">
            <ListData
              name={selectors.STORE_NAME + '.connections'}
              fetch={{
                url: this.props.listUrl,
                autoload: true
              }}
              definition={[
                {
                  name: 'user',
                  type: 'user',
                  label: trans('user'),
                  displayed: true
                }, {
                  name: 'date',
                  alias: 'connectionDate',
                  type: 'date',
                  label: trans('date'),
                  displayed: true,
                  filterable: false,
                  primary: true,
                  options: {
                    time: true
                  }
                }, {
                  name: 'duration',
                  type: 'string',
                  label: trans('duration'),
                  displayed: true,
                  filterable: false,
                  calculated: (row) => row.duration !== null ? displayDuration(row.duration) : null
                }
              ]}
              display={{
                available: [listConst.DISPLAY_TABLE, listConst.DISPLAY_TABLE_SM],
                current: listConst.DISPLAY_TABLE
              }}
              selectable={false}
            />
          </div>
        </div>
      </Fragment>
    )
  }
}

ConnectionTimeChart.propTypes = {
  url: T.oneOfType([T.string, T.array]).isRequired,
  listUrl: T.oneOfType([T.string, T.array]).isRequired,

  loaded: T.bool.isRequired,
  data: T.shape({
    total: T.object, // todo check
    average: T.object
  }),
  fetchConnectionTime: T.func.isRequired
}

export {
  ConnectionTimeChart
}