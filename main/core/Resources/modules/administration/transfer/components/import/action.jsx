import React, {Component} from 'react'

import {PageActions} from '#/main/core/layout/page/components/page-actions.jsx'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'
import {actions} from '#/main/core/administration/transfer/components/log/actions'

import {connect} from 'react-redux'

class Action extends Component {
  constructor(props) {
    super(props)
    this.currentLogId = this.generateLogId()
  }

  generateLogId() {
    const log = Math.random().toString(36).substring(7)
    this.currentLogId = log

    return log
  }

  getLogId() {
    return this.currentLogId
  }


  render() {
    return(
      <PageActions>
        <FormPageActionsContainer
          formName="import"
          target={['apiv2_transfer_start', {log: this.getLogId()}]}
          opened={true}
          save={{
            type: 'callback',
            callback: () => {
              const logName = this.getLogId()
              const refresher = setInterval(() => {
                this.props.loadLog(logName)
                if (this.props.data && this.props.data.total !== undefined && this.props.data.processed === this.props.data.total) {
                  clearInterval(refresher)
                }
              }, 2000)

              this.generateLogId()
            }
          }}
        />
      </PageActions>
    )
  }
}


const ConnectedAction = connect(
  state => ({
    data: state.log
  }),
  dispatch => ({
    loadLog(filename) {
      dispatch(actions.load(filename))
    }
  })
)(Action)

export {
  ConnectedAction as Action
}
