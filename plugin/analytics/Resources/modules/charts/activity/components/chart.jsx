import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {schemeCategory20c} from 'd3-scale'

import {trans} from '#/main/app/intl/translation'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {LineChart} from '#/main/core/layout/chart/line/components/line-chart'

class ActivityChart extends Component {
  constructor(props) {
    super(props)

    this.state = {
      range: 'week'
    }
  }

  componentDidMount() {
    if (!this.props.loaded) {
      this.props.fetchActivity(this.props.url)
    }
  }

  changeRange(range) {
    this.setState({range: range})

    // TODO : add range
    this.props.fetchActivity(this.props.url)
  }

  render() {
    return (
      <div className="panel panel-default panel-analytics panel-activity">
        <div className="panel-heading">
          <h2 className="panel-title">
            {trans('recent_activity', {}, 'analytics')}
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

        <div className="panel-body text-right" style={{paddingTop: '11px'}}>
          <Toolbar
            id="activity-legend"
            className="chart-legend"
            buttonName="btn-link"
            actions={[
              {
                name: 'actions',
                type: CALLBACK_BUTTON,
                icon: <span className="action-icon fa fa-fw fa-circle icon-with-text-right" style={{color: schemeCategory20c[0]}} />,
                label: trans('actions'),
                callback: () => true
              }, {
                name: 'visitors',
                type: CALLBACK_BUTTON,
                icon: <span className="action-icon fa fa-fw fa-circle icon-with-text-right" style={{color: schemeCategory20c[4]}} />,
                label: trans('visitors'),
                callback: () => true
              }, {
                name: 'duration',
                type: CALLBACK_BUTTON,
                icon: <span className="action-icon fa fa-fw fa-circle icon-with-text-right" style={{color: schemeCategory20c[8]}} />,
                label: trans('connection_time'),
                callback: () => true,
                displayed: false // TODO
              }
            ]}
          />

          <LineChart
            data={[
              this.props.data.actions,
              this.props.data.visitors
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
            width={936}
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