import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import Modal from 'react-bootstrap/lib/Modal'
import classes from 'classnames'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'
import {taskTypes} from '../enums'
import {navigate} from '../router'

export const MODAL_TASK_TYPE_FORM = 'MODAL_TASK_TYPE_FORM'

class TaskTypeChoice extends Component {
  render() {
    return (
      <div
        className={classes('task-type-container', {'selected': this.props.selected})}
        onMouseOver={() => this.props.handleTypeMouseOver(this.props.type)}
        onClick={() => navigate(this.props.type.type, true)}
      >
        <span className={classes('task-type-icon', this.props.type.icon)}></span>
      </div>
    )
  }
}

TaskTypeChoice.propTypes = {
  type: T.shape({
    type: T.string.isRequired,
    name: T.string.isRequired,
    icon: T.string
  }).isRequired,
  selected: T.bool.isRequired,
  handleTypeMouseOver: T.func.isRequired
}

export class TaskTypeFormModal  extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentType: taskTypes[0],
      currentTypeName: taskTypes[0].name
    }
  }

  handleTypeMouseOver(type) {
    this.setState({
      currentType: type,
      currentTypeName: type.name
    })
  }

  render() {
    return (
      <BaseModal {...this.props} className="task-type-form-modal">
        <Modal.Body>
          <div className="task-type-list">
            {taskTypes.map(tt =>
              <TaskTypeChoice
                key={tt.type}
                type={tt}
                selected={this.state.currentType.type === tt.type}
                handleTypeMouseOver={() => this.handleTypeMouseOver(tt)}
              />
            )}
          </div>
          <div className="task-type-desc">
            <span className="task-type-name">
              {this.state.currentTypeName}
            </span>
          </div>
        </Modal.Body>
      </BaseModal>
    )
  }
}

TaskTypeFormModal.propTypes = {
  fadeModal: T.func.isRequired
}