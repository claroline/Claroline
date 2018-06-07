import React from 'react'
import {PropTypes as T} from 'prop-types'
import {schemeCategory20c} from 'd3-scale'

import {trans} from '#/main/core/translation'
import {Heading} from '#/main/core/layout/components/heading'
import {PieChart} from '#/main/core/layout/chart/pie/components/pie-chart'

import {ResourceIcon} from '#/main/core/resource/components/icon'

const Resources = props => {
  const chartData = Object.keys(props.resourceTypes).map(v => props.resourceTypes[v]) || []

  return (
    <section>
      <Heading level={2}>
        {trans('resources_usage_ratio')}
      </Heading>

      <div className="row">
        <div className="col-md-6">
          <PieChart
            data={props.resourceTypes}
            width={600}
            margin={{
              top: 0,
              right: 0,
              bottom: 0,
              left: 0
            }}
            responsive={true}
            colors={schemeCategory20c}
            showPercentage={true}
          />
        </div>

        <div className="col-md-6">
          <ul className="chart-legend">
            {chartData.map((data, idx) =>
              <li key={idx}>
                <span
                  className="legend-color"
                  style={{
                    backgroundColor: schemeCategory20c[idx % schemeCategory20c.length]
                  }}
                />

                <div className="legend-label">
                  <ResourceIcon className="legend-icon" mimeType={`custom/${data.xData}`} />
                  {trans(data.xData, {}, 'resource')}
                </div>

                <span className="legend-value">{data.yData}</span>
              </li>
            )}

            <li className="total">
              <div className="legend-label">
                <span className="legend-icon fa fa-folder" />

                Total
              </div>

              <span className="legend-value">{chartData.reduce((total, current) => total + current.yData, 0)}</span>
            </li>
          </ul>
        </div>
      </div>
    </section>
  )
}

Resources.propsTypes = {
  resourceTypes: T.object.isRequired // todo check
}

export {
  Resources
}