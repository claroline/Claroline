import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {schemeCategory20c} from 'd3-scale'
import moment from 'moment'

import {trans, apiDate} from '#/main/app/intl'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {LineChart} from '#/main/core/layout/chart/line/components/line-chart'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'

import {selectors} from '#/plugin/analytics/charts/actions/store/selectors'

class ActionsChart extends Component {
  constructor(props) {
    super(props)

    this.state = {
      range: 'week'
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

  refreshData() {
    this.props.fetchActions(
      this.props.url,
      apiDate(moment().subtract(1, this.state.range)),
      apiDate(moment())
    )
  }

  render() {
    return (
      <Fragment>
        <div className="panel panel-default panel-analytics">
          <div className="panel-heading">
            <h2 className="panel-title">
              {trans('activity')}
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

          <div className="panel-body text-right">
            <LineChart
              data={[this.props.data.actions]}
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
              colors={schemeCategory20c}
            />
          </div>
        </div>

        <div className="panel panel-default embedded-list-section">
          <div className="panel-body">
            <ListData
              name={selectors.STORE_NAME + '.logs'}
              fetch={{
                url: this.props.listUrl,
                autoload: true
              }}
              definition={[
                {
                  name: 'action',
                  type: 'enum-plus',
                  label: trans('action'),
                  options: {
                    choices: this.props.data.types || [],
                    transDomain: 'log'
                  }
                }, {
                  name: 'doer',
                  type: 'user',
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
                }, {
                  name: 'dateLog',
                  type: 'date',
                  label: trans('date'),
                  displayed: true,
                  primary: true,
                  filterable: false,
                  options: {
                    time: true
                  }
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

ActionsChart.propTypes = {
  url: T.oneOfType([T.string, T.array]).isRequired,
  listUrl: T.oneOfType([T.string, T.array]).isRequired,

  loaded: T.bool.isRequired,
  data: T.shape({
    types: T.object, // todo check
    actions: T.object
  }),
  fetchActions: T.func.isRequired
}

export {
  ActionsChart
}