import React from 'react'
import {PropTypes as T} from 'prop-types'
import {WorkspaceCreation} from '#/main/app/contexts/workspace/components/creation'

const CreationType = (props) =>
  <div className="modal-body">
    <WorkspaceCreation
      startCreation={props.startCreation}
      changeStep={props.changeStep}
    />
  </div>

CreationType.propTypes = {
  startCreation: T.func.isRequired,
  changeStep: T.func.isRequired
}

export {
  CreationType
}
