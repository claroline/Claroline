import React from 'react'
import {connect} from 'react-redux'

const MaintenanceComponent = () =>
  <div>
    Maintenance
  </div>


MaintenanceComponent.propTypes = {
}

const Maintenance = connect(
  null,
  () => ({ })
)(MaintenanceComponent)

export {
  Maintenance
}
