import React from 'react'
import {PropTypes as T} from 'prop-types'

const DashboardCard = (props) =>
  <div className={'dashboard-card data-card data-card-col'}>
    <div className={'data-card-header'}>
      <div className={'data-card-title text-left'}>
        {props.icon && <i className={`fa ${props.icon}`}/>}
        <span>{props.title}</span>
      </div>
    </div>
    <div className={'data-card-content'}>
      {props.children}
    </div>
  </div>

DashboardCard.propTypes = {
  title: T.string.isRequired,
  icon: T.string,
  children: T.node.isRequired
}

export {
  DashboardCard
}