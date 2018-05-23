import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {Grid, Row, Col} from 'react-bootstrap'
import {schemeCategory20c} from 'd3-scale'
import {trans} from '#/main/core/translation'
import {LineChart} from '#/main/core/layout/chart/line/components/line-chart.jsx'
import {PieChart} from '#/main/core/layout/chart/pie/components/pie-chart.jsx'
import {actions} from '#/main/core/tools/workspace/dashboard/actions'
import {DashboardTable, DashboardCard} from '#/main/core/layout/dashboard'

class Dashboard extends Component {
  constructor(props) {
    super(props)

    if (!props.dashboard.loaded) {
      props.getDashboard(props.workspaceId)
    }
  }

  render() {
    const props = this.props
    return(
      <Grid className={'dashboard-container'}>
        <Row className={'dashboard-row'}>
          <Col xs={12}>
            <DashboardCard title={trans('last_30_days_activity')} icon={'fa-area-chart'}>
              <LineChart
                style={{maxHeight: 250}}
                responsive={true}
                data={props.dashboard.data.activity}
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
        <Row>
          <Col sm={12} md={6}>
            <DashboardCard title={trans('resources_usage_ratio')} icon={'fa-pie-chart'}>
              <PieChart
                style={{
                  margin: 'auto'
                }}
                data={props.dashboard.data.resourceTypes || {}}
                width={400}
                margin={{
                  top:25
                }}
                colors={schemeCategory20c}
                showPercentage={true}
              />
            </DashboardCard>
          </Col>
          <Col sm={12} md={6}>
            <DashboardCard title={trans('resources_usage_list')} icon={'fa-list'}>
              <DashboardTable
                definition={[
                  {
                    name: 'xData',
                    label: trans('name'),
                    transDomain: 'resource',
                    colorLegend: true
                  }, {
                    name: 'yData',
                    label: '#'
                  }
                ]}
                data={
                  (
                    props.dashboard.data.resourceTypes &&
                    Object.keys(props.dashboard.data.resourceTypes).map(v => props.dashboard.data.resourceTypes[v])
                  ) || []
                }
                colors={schemeCategory20c}
              />
            </DashboardCard>
          </Col>
        </Row>
      </Grid>
    )
  }
}

Dashboard.propTypes = {
  dashboard: T.shape({
    loaded: T.bool.isRequired,
    data: T.object
  }).isRequired,
  workspaceId: T.number.isRequired,
  getDashboard: T.func.isRequired
}

const DashboardContainer  = connect(
  state => ({
    dashboard: state.dashboard,
    workspaceId: state.workspaceId
  }),
  disptach => ({
    getDashboard: (workspaceId) => {
      disptach(actions.getDashboardData('apiv2_workspace_tool_dashboard', {workspaceId}))
    }
  })
)(Dashboard)

export {
  DashboardContainer as Dashboard
}