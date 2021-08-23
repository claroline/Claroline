import React, {Component, Fragment} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {withRouter} from '#/main/app/router'
import {DropzoneType, DropType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {generateCorrectionGrades} from '#/plugin/drop-zone/resources/dropzone/utils'
import {actions} from '#/plugin/drop-zone/resources/dropzone/player/store/actions'
import {actions as correctionActions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'
import {Documents} from '#/plugin/drop-zone/resources/dropzone/components/documents'
import {CorrectionForm} from '#/plugin/drop-zone/resources/dropzone/correction/components/correction-form'

class PeerDrop extends Component {
  constructor(props) {
    super(props)

    this.saveCorrection = this.saveCorrection.bind(this)
    this.cancelCorrection = this.cancelCorrection.bind(this)
  }

  saveCorrection(correction) {
    this.props.saveCorrection(correction)
  }

  cancelCorrection(navigate) {
    navigate(this.props.path)
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
    if (this.props.drop) {
      return (
        <Fragment>
          <Documents
            documents={this.props.drop.documents}
            canEdit={false}
            showMeta={false}
            {...this.props}
          />
          <CorrectionForm
            navigate={this.props.history.push}
            correction={generateCorrectionGrades(this.getCorrection(), this.props.dropzone)}
            dropzone={this.props.dropzone}
            saveCorrection={this.saveCorrection}
            showSubmitButton={true}
            submitCorrection={(correctionId, navigate) => this.props.submitCorrection(correctionId, navigate, this.props.path)}
            cancelCorrection={this.cancelCorrection}
          />
        </Fragment>
      )
    }

    return (
      <div className="alert alert-warning">
        {trans('no_copy_to_correct', {}, 'dropzone')}
      </div>
    )
  }
}

PeerDrop.propTypes = {
  path: T.string.isRequired,
  dropzone: T.shape(DropzoneType.propTypes).isRequired,
  drop: T.shape(DropType.propTypes),
  user: T.shape({
    id: T.string.isRequired
  }),
  myTeamId: T.number,
  saveCorrection: T.func.isRequired,
  submitCorrection: T.func.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired
}

const ConnectedPeerDrop = withRouter(
  connect(
    (state) => ({
      user: selectors.user(state),
      dropzone: selectors.dropzone(state),
      drop: selectors.peerDrop(state),
      myTeamId: selectors.myTeamId(state)
    }),
    (dispatch) => ({
      saveCorrection: (correction) => dispatch(correctionActions.saveCorrection(correction)),
      submitCorrection: (correctionId, navigate, path) => dispatch(actions.submitCorrection(correctionId, navigate, path)).then(() => {
        navigate(path)
      })
    })
  )(PeerDrop)
)

export {ConnectedPeerDrop as PeerDrop}
