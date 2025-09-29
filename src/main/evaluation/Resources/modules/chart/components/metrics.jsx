import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {constants} from '#/main/app/constants'
import {LinkButton} from '#/main/app/buttons'
import {Html} from '#/main/app/components/html'

const MetricsChartCard = ({className, icon, title, color, primaryValue, secondaryValue, moreLink, loaded}) =>
  <div className={classes('px-4 col d-flex flex-column align-items-start', className, {'placeholder-glow': !loaded})}>
    <h3 className="h6">
      {icon &&
        <span className={`fa fa-fw fa-${icon} me-2`} style={{color: color}}/>
      }

      {title}
    </h3>

    <div className={classes('h2 fw-semibold mb-0', {'placeholder w-25': !loaded})}>
      {primaryValue}
    </div>

    {!loaded &&
      <div className="text-body-secondary w-100">
        <div className="placeholder w-50" />
      </div>
    }

    {loaded && secondaryValue &&
      <Html className={classes('text-body-secondary')}>{secondaryValue}</Html>
    }

    {moreLink &&
      <LinkButton className="btn btn-link ms-auto me-n3 mb-n2 mt-auto" target={moreLink} exact={true}>
        {trans('see_more', {}, 'actions')}
        <span className="ms-2 fa fa-arrow-right" aria-hidden={true} />
      </LinkButton>
    }
  </div>

MetricsChartCard.propTypes = {
  className: T.string,
  title: T.string.isRequired,
  icon: T.string,
  primaryValue: T.any.isRequired,
  secondaryValue: T.any,
  moreLink: T.string,
  loaded: T.bool
}

const MetricsChart = ({className, loaded = true, data = []}) => {
  const metrics = data.filter(metric => undefined === metric.displayed || metric.displayed)

  return (
    <div className={classes('d-flex flex-row my-4 mx-n4', className)}>
      {metrics.map((metric, index) => (
        <MetricsChartCard
          {...metric}
          className={classes({'border-start': 0 !== index})}
          color={constants.COLORS[index]}
          loaded={loaded}
        />
      ))}
    </div>
  )
}

MetricsChart.propTypes = {
  className: T.string,
  data: T.arrayOf(T.shape({
    title: T.string.isRequired,
    icon: T.string,
    primaryValue: T.any.isRequired,
    secondaryValue: T.any,
    moreLink: T.string,
    displayed: T.bool
  })),
  loaded: T.bool
}

export {
  MetricsChart
}
