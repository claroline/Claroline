import React from 'react'
import {PropTypes as T} from 'prop-types'

const UnpublishedComponent = () =>
  <div key='redactors' className="panel panel-default">
    unpublished
  </div>

UnpublishedComponent.propTypes = {
  calendarSelectedDate: T.string,
  searchByDate: T.func.isRequired
}


export {UnpublishedComponent as Unpublished}