import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {navigate} from '#/main/app/router'

import {DropzoneType, DropType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {select} from '#/plugin/drop-zone/resources/dropzone/selectors'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {generateCorrectionGrades} from '#/plugin/drop-zone/resources/dropzone/utils'
import {actions} from '#/plugin/drop-zone/resources/dropzone/player/actions'
import {actions as correctionActions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'
import {Documents} from '#/plugin/drop-zone/resources/dropzone/components/documents.jsx'
import {CorrectionForm} from '#/plugin/drop-zone/resources/dropzone/correction/components/correction-form.jsx'

class PeerDrop extends Component {
  constructor(props) {
    super(props)
    this.saveCorrection = this.saveCorrection.bind(this)
    this.cancelCorrection = this.cancelCorrection.bind(this)
  }

  saveCorrection(correction) {
    this.props.saveCorrection(correction)
  }

  cancelCorrection() {
    navigate('/')
  }

  getCorrection() {
    let drop = null

    switch (this.props.dropzone.parameters.dropType) {
      case constants.DROP_TYPE_USER :
        drop = this.props.drop.corrections.find(c => !c.finished && c.user.id === this.props.user.id)
        break
      case constants.DROP_TYPE_TEAM :
        if (this.props.myTeamId) {
          drop = this.props.drop.corrections.find(c => !c.finished && c.teamId === this.props.myTeamId)
        }
        break
    }

    return drop
  }

  render() {
    return (this.props.drop ?
      <div className="drop-panel">
        <Documents
          documents={this.props.drop.documents}
          canEdit={false}
          showMeta={false}
          {...this.props}
        />
        <CorrectionForm
          correction={generateCorrectionGrades(this.getCorrection(), this.props.dropzone)}
          dropzone={this.props.dropzone}
          saveCorrection={this.saveCorrection}
          showSubmitButton={true}
          submitCorrection={this.props.submitCorrection}
          cancelCorrection={this.cancelCorrection}
        />
      </div> :
      <div className="alert alert-warning">
        {trans('no_copy_to_correct', {}, 'dropzone')}
      </div>
    )
  }
}

PeerDrop.propTypes = {
  dropzone: T.shape(DropzoneType.propTypes).isRequired,
  drop: T.shape(DropType.propTypes),
  user: T.shape({
    id: T.string.isRequired
  }),
  myTeamId: T.number,
  saveCorrection: T.func.isRequired,
  submitCorrection: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    user: select.user(state),
    dropzone: select.dropzone(state),
    drop: select.peerDrop(state),
    myTeamId: select.myTeamId(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    saveCorrection: (correction) => dispatch(correctionActions.saveCorrection(correction)),
    submitCorrection: (correctionId) => dispatch(actions.submitCorrection(correctionId))
  }
}

const ConnectedPeerDrop = connect(mapStateToProps, mapDispatchToProps)(PeerDrop)

export {ConnectedPeerDrop as PeerDrop}
