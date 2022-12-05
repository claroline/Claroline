import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {schemeCategory20c} from 'd3-scale'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {PieChart} from '#/main/core/layout/chart/pie/components/pie-chart'
import {DataTable} from '#/main/app/content/list/components/view/data-table'

class UsersChart extends Component {
  constructor(props) {
    super(props)

    this.state = {
      sort: {property: 'count', direction: -1}
    }
  }

  componentDidMount() {
    if (!this.props.loaded) {
      this.props.fetchUsers(this.props.url)
    }
  }

  render() {
    let chartData = []
    if (this.props.loaded) {
      //chartData = Object.keys(this.props.data).map(v => this.props.data[v])
      chartData = this.props.data
    }

    return (
      <div className="panel panel-default panel-analytics">
        <div className="panel-heading">
          <h2 className="panel-title">
            {trans('users')}
          </h2>

          <nav className="panel-actions">
            <Button
              className="btn-link"
              type={CALLBACK_BUTTON}
              icon="fa fa-fw fa-table-list"
              label={trans('list')}
              callback={() => this.props.changeMode('list')}
              active={'list' === this.props.mode}
              tooltip="bottom"
            />

            <Button
              className="btn-link"
              type={CALLBACK_BUTTON}
              icon="fa fa-fw fa-chart-pie"
              label={trans('chart')}
              callback={() => this.props.changeMode('chart')}
              active={'chart' === this.props.mode}
              tooltip="bottom"
            />
          </nav>
        </div>

        {this.props.loaded && 'chart' === this.props.mode &&
          <div className="panel-body">
            <PieChart
              data={this.props.data.map(v => v.total) || {}}
              width={300}
              innerRadius={3}
              showValue={false}
              margin={{
                top: 0,
                right: 0,
                bottom: 0,
                left: 0
              }}
              responsive={true}
              colors={schemeCategory20c}
              showPercentage={true}
            >
              <g transform="translate(-25, -25)">
                <circle cx="25" cy="25" r="50" fill="black" fillOpacity={0.25} />
                <circle cx="25" cy="25" r="48" fill="white" />
                <text fontSize="40" transform="translate(7, 40)" fill="#444444">
                  <tspan className="fa fa-user">&#xf007;</tspan>
                </text>
              </g>
            </PieChart>
          </div>
        }

        {this.props.loaded && 'list' === this.props.mode &&
          <div
            style={{
              maxHeight: '315px',
              overflowY: 'auto',
              overflowX: 'hidden'
            }}
          >
            <DataTable
              count={chartData.reduce((total, current) => total + current.total, 0)}
              data={chartData
                .map((data, idx) => ({
                  id: data.xData,
                  color: schemeCategory20c[idx % schemeCategory20c.length],
                  type: trans(data.name),
                  count: data.total
                }))
                .sort((a, b) => {
                  if (a[this.state.sort.property] < b[this.state.sort.property]) {
                    return this.state.sort.direction * -1
                  } else if (a[this.state.sort.property] > b[this.state.sort.property]) {
                    return this.state.sort.direction
                  }

                  return 0
                })
              }
              columns={[
                // <ResourceIcon className="legend-icon" mimeType={`custom/${data.xData}`} />
                {
                  name: 'color',
                  label: trans('color'),
                  type: 'color',
                  sortable: false
                }, {
                  name: 'type',
                  label: trans('type'),
                  type: 'string',
                  sortable: true
                }, {
                  name: 'count',
                  label: trans('#'),
                  type: 'number',
                  sortable: true
                }
              ]}
              sorting={{
                current: this.state.sort,
                updateSort: (property, direction) => this.setState({sort: {property: property, direction: direction}})
              }}
            />
          </div>
        }
      </div>
    )
  }
}


UsersChart.propTypes = {
  url: T.oneOfType([T.string, T.array]).isRequired,

  loaded: T.bool.isRequired,
  mode: T.oneOf(['chart', 'list']),
  data: T.arrayOf(T.shape({

  })),
  changeMode: T.func.isRequired,
  fetchUsers: T.func.isRequired
}

export {
  UsersChart
}
