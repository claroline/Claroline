import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {schemeCategory20c} from 'd3-scale'
import {Grid, Row, Col} from 'react-bootstrap'

import {trans} from '#/main/core/translation'
import {PieChart} from '#/main/core/layout/chart/pie/components/pie-chart.jsx'
import {actions} from '#/main/core/administration/analytics/actions'
import {DashboardTable, DashboardCard} from '#/main/core/layout/dashboard/index'

class Tab extends Component {
  constructor(props) {
    super(props)
    
    if (!props.resources.loaded) {
      props.getResourcesData()
    }
  }
  
  render() {
    return(
      <Grid className="analytics-resources-container">
        {this.props.resources.data.workspaces &&
        <Row>
          <Col xs={12}>
            <div className={'dashboard-standout'}>
              <span className={'dashboard-standout-text-lg'}>
                {this.props.resources.data.workspaces}
              </span>
              <span className={'dashboard-standout-text-sm'}>
                <i className={'fa fa-book'}/>
                <span>{trans('workspaces')}</span>
              </span>
            </div>
          </Col>
        </Row>
        }
        <Row>
          <Col xs={12} md={6}>
            <DashboardCard title={trans('resources_usage_ratio')} icon={'fa-pie-chart'}>
              <PieChart
                style={{
                  margin: 'auto',
                  maxHeight: 400
                }}
                data={this.props.resources.data.resources || {}}
                width={400}
                margin={{
                  top: 25
                }}
                colors={schemeCategory20c}
                showPercentage={true}
                responsive={true}
              />
            </DashboardCard>
          </Col>
          <Col xs={12} md={6}>
            <DashboardCard title={trans('resources_usage_list')} icon={'fa-list'}>
              {this.props.resources.data.resources && Object.keys(this.props.resources.data.resources).length > 0 &&
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
                data={Object.keys(this.props.resources.data.resources).map(v => this.props.resources.data.resources[v])}
                colors={schemeCategory20c}
              />
              }
              {this.props.resources.data.other && Object.keys(this.props.resources.data.other).length > 0 &&
              <DashboardTable
                definition={[
                  {
                    name: 'xData',
                    label: trans('others')
                  }, {
                    name: 'yData',
                    label: '#'
                  }
                ]}
                data={Object.keys(this.props.resources.data.other).map(v => this.props.resources.data.other[v])}
              />
              }
            </DashboardCard>
          </Col>
        </Row>
      </Grid>
    )
  }
}

Tab.propTypes = {
  resources: T.shape({
    loaded: T.bool.isRequired,
    data: T.object
  }).isRequired,
  getResourcesData: T.func.isRequired
}

const TabContainer = connect(
  state => ({
    resources: state.resources
  }),
  dispatch => ({
    getResourcesData() {
      dispatch(actions.getResourcesData())
    }
  })
)(Tab)

export {
  TabContainer as ResourcesTab
}
