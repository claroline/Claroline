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
    
    if (!props.widgets.loaded) {
      props.getWidgetsData()
    }
  }
  
  render() {
    return(
      <Grid className="analytics-widgets-container">
        <Row>
          <Col sm={4}>
            <div className={'dashboard-standout'}>
              <span className={'dashboard-standout-text-lg'}>
                {this.props.widgets.data.all}
              </span>
              <span className={'dashboard-standout-text-sm'}>
                <span>{trans('widgets')}</span>
              </span>
            </div>
          </Col>
          <Col sm={4}>
            <div className={'dashboard-standout'}>
              <span className={'dashboard-standout-text-lg'}>
                {this.props.widgets.data.workspace}
              </span>
              <span className={'dashboard-standout-text-sm'}>
                <span>{trans('in_workspaces')}</span>
              </span>
            </div>
          </Col>
          <Col sm={4}>
            <div className={'dashboard-standout'}>
              <span className={'dashboard-standout-text-lg'}>
                {this.props.widgets.data.desktop}
              </span>
              <span className={'dashboard-standout-text-sm'}>
                <span>{trans('on_desktops')}</span>
              </span>
            </div>
          </Col>
        </Row>
        {this.props.widgets.data.list && this.props.widgets.data.list.length > 0 &&
        <Row>
          <Col xs={12} md={6}>
            <DashboardCard title={trans('widgets_usage_ratio')} icon={'fa-pie-chart'}>
              <PieChart
                style={{
                  margin: 'auto',
                  maxHeight: 400
                }}
                data={this.props.widgets.data.list.map(v => parseFloat(v.total))}
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
            <DashboardCard title={trans('widgets_usage_list')} icon={'fa-list'}>
              <DashboardTable
                definition={[
                  {
                    name: 'name',
                    label: trans('widget_type'),
                    transDomain: 'widget',
                    colorLegend: true
                  }, {
                    name: 'totalByWorkspace',
                    label: trans('workspaces')
                  },
                  {
                    name: 'totalByDesktop',
                    label: trans('desktops')
                  },
                  {
                    name: 'total',
                    label: trans('total')
                  }
                ]}
                data={this.props.widgets.data.list}
                colors={schemeCategory20c}
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
  widgets: T.shape({
    loaded: T.bool.isRequired,
    data: T.object
  }).isRequired,
  getWidgetsData: T.func.isRequired
}

const TabContainer = connect(
  state => ({
    widgets: state.widgets
  }),
  dispatch => ({
    getWidgetsData() {
      dispatch(actions.getWidgetsData())
    }
  })
)(Tab)

export {
  TabContainer as WidgetsTab
}
