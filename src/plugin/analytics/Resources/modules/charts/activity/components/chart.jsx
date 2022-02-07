import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {schemeCategory20c} from 'd3-scale'
import moment from 'moment'

import {trans, apiDate} from '#/main/app/intl'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {LineChart} from '#/main/core/layout/chart/line/components/line-chart'

class ActivityChart extends Component {
  constructor(props) {
    super(props)

    this.state = {
      range: 'week',
      display: [
        'actions',
        'visitors'
      ]
    }
  }

  componentDidMount() {
    if (!this.props.loaded) {
      this.refreshData()
    }
  }

  changeRange(range) {
    this.setState({range: range}, () => {
      this.refreshData()
    })
  }

  toggleDisplay(display) {
    const newDisplay = [].concat(this.state.display)

    const displayPos = newDisplay.indexOf(display)
    if (-1 === displayPos) {
      newDisplay.push(display)
    } else {
      newDisplay.splice(displayPos, 1)
    }

    this.setState({display: newDisplay})
  }

  refreshData() {
    this.props.fetchActivity(
      this.props.url,
      apiDate(moment().startOf(this.state.range)),
      apiDate(moment().endOf(this.state.range))
    )
  }

  render() {
    return (
      <div className="panel panel-default panel-analytics">
        <div className="panel-heading">
          <h2 className="panel-title">
            {trans('recent_activity', {}, 'analytics')}
          </h2>

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
        </div>

        <div className="panel-body text-right" style={{paddingTop: '11px'}}>
          <Toolbar
            id="activity-legend"
            className="chart-legend"
            buttonName="btn-link"
            actions={[
              {
                name: 'actions',
                type: CALLBACK_BUTTON,
                icon: <span className="action-icon fa fa-fw fa-circle icon-with-text-right" style={{color: -1 !== this.state.display.indexOf('actions') ? schemeCategory20c[0] : '#CCCCCC'}} />,
                label: trans('actions'),
                callback: () => this.toggleDisplay('actions')
              }, {
                name: 'visitors',
                type: CALLBACK_BUTTON,
                icon: <span className="action-icon fa fa-fw fa-circle icon-with-text-right" style={{color: -1 !== this.state.display.indexOf('visitors') ? schemeCategory20c[4] : '#CCCCCC'}} />,
                label: trans('visitors'),
                callback: () => this.toggleDisplay('visitors')
              }, {
                name: 'duration',
                type: CALLBACK_BUTTON,
                icon: <span className="action-icon fa fa-fw fa-circle icon-with-text-right" style={{color: -1 !== this.state.display.indexOf('duration') ? schemeCategory20c[8] : '#CCCCCC'}} />,
                label: trans('connection_time'),
                callback: () => this.toggleDisplay('duration'),
                displayed: false // TODO
              }
            ]}
          />

          <LineChart
            data={[
              -1 !== this.state.display.indexOf('actions') && this.props.data.actions,
              -1 !== this.state.display.indexOf('visitors') && this.props.data.visitors
            ].filter(value => !!value)}
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
            width={936}
            showArea={true}
            margin={{
              left: 25,
              top: 5,
              right: 0,
              bottom: 30
            }}
            colors={[
              -1 !== this.state.display.indexOf('actions') && schemeCategory20c[0],
              -1 !== this.state.display.indexOf('visitors') && schemeCategory20c[4]
            ].filter(value => !!value)}
          />
        </div>
      </div>
    )
  }
}


ActivityChart.propTypes = {
  url: T.oneOfType([T.string, T.array]).isRequired,

  loaded: T.bool.isRequired,
  data: T.shape({
    actions: T.object, // todo check
    visitors: T.object
  }),
  fetchActivity: T.func.isRequired
}

export {
  ActivityChart
}