import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {schemeCategory20c} from 'd3-scale'
import {Grid, Row, Col} from 'react-bootstrap'

import {trans} from '#/main/core/translation'
import {LineChart} from '#/main/core/layout/chart/line/components/line-chart.jsx'
import {PieChart} from '#/main/core/layout/chart/pie/components/pie-chart.jsx'
import {actions} from '#/main/core/administration/analytics/actions'
import {DashboardTable, DashboardCard} from '#/main/core/layout/dashboard/index'

class Tab extends Component {
  constructor(props) {
    super(props)
    
    if (!props.overview.loaded) {
      props.getOverviewData()
    }
  }
  
  render() {
    return(
      <Grid className="analytics-overview-container">
        <Row>
          <Col xs={12}>
            <DashboardCard title={trans('last_30_days_activity')} icon={'fa-area-chart'}>
              <LineChart
                style={{maxHeight: 250}}
                responsive={true}
                data={this.props.overview.data.activity}
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
                width={800}
                showArea={true}
                margin={{
                  top: 20,
                  bottom: 50,
                  left: 50,
                  right: 20
                }}
              />
            </DashboardCard>
          </Col>
        </Row>
        {this.props.overview.data.users &&
          <Row>
            <Col xs={12} md={6}>
              <DashboardCard title={trans('account_general_statistics')} icon={'fa-user'}>
                <div className="dashboard-standout text-center">
                  <span className="dashboard-standout-text-lg">{this.props.overview.data.totalUsers}</span>
                  <span className="dashboard-standout-text-sm">{trans('user_accounts')}</span>
                </div>
                <DashboardTable
                  definition={[
                    {
                      name: 'name',
                      label: trans('role'),
                      colorLegend: true,
                      transDomain: 'platform'
                    }, {
                      name: 'total',
                      label: '#'
                    }
                  ]}
                  data={this.props.overview.data.users}
                  colors={schemeCategory20c}
                />
              </DashboardCard>
            </Col>
            <Col xs={12} md={6}>
              <DashboardCard title={trans('account_general_statistics')} icon={'fa-pie-chart'}>
                <PieChart
                  style={{
                    margin: 'auto',
                    maxHeight: 350
                  }}
                  data={this.props.overview.data.users.map(v => v.total) || {}}
                  width={350}
                  margin={{
                    top: 25
                  }}
                  colors={schemeCategory20c}
                  showPercentage={true}
                  responsive={true}
                />
              </DashboardCard>
            </Col>
          </Row>
        }
        {
          this.props.overview.data.top &&
          this.props.overview.data.top.workspace &&
          this.props.overview.data.top.workspace.length > 0 &&
          <Row>
            <Col xs={12}>
              <DashboardCard title={trans('ws_most_viewed')} icon={'fa-book'}>
                <DashboardTable
                  definition={[
                    {
                      name: 'name',
                      label: trans('name')
                    }, {
                      name: 'actions',
                      label: trans('connections')
                    }
                  ]}
                  data={this.props.overview.data.top.workspace}
                />
              </DashboardCard>
            </Col>
          </Row>
        }
        {
          this.props.overview.data.top &&
          this.props.overview.data.top.media &&
          this.props.overview.data.top.media.length > 0 &&
          <Row>
            <Col xs={12}>
              <DashboardCard title={trans('media_most_viewed')} icon={'fa-file'}>
                <DashboardTable
                  definition={[
                    {
                      name: 'name',
                      label: trans('name')
                    }, {
                      name: 'actions',
                      label: trans('views')
                    }
                  ]}
                  data={this.props.overview.data.top.media}
                />
              </DashboardCard>
            </Col>
          </Row>
        }
        {
          this.props.overview.data.top &&
          this.props.overview.data.top.download &&
          this.props.overview.data.top.download.length > 0 &&
          <Row>
            <Col xs={12}>
              <DashboardCard title={trans('resources_most_downloaded')} icon={'fa-download'}>
                <DashboardTable
                  definition={[
                    {
                      name: 'name',
                      label: trans('name')
                    }, {
                      name: 'actions',
                      label: trans('downloads')
                    }
                  ]}
                  data={this.props.overview.data.top.download}
                />
              </DashboardCard>
            </Col>
          </Row>
        }
      </Grid>
    )
  }
}

Tab.propTypes = {
  overview: T.shape({
    loaded: T.bool.isRequired,
    data: T.object
  }).isRequired,
  getOverviewData: T.func.isRequired
}

const TabContainer = connect(
  state => ({
    overview: state.overview
  }),
  dispatch => ({
    getOverviewData() {
      dispatch(actions.getOverviewData())
    }
  })
)(Tab)

export {
  TabContainer as OverviewTab
}
