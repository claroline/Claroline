import React from 'react'
import moment from 'moment'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {DropzoneType, DropType, CorrectionType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {getCorrectionKey} from '#/plugin/drop-zone/resources/dropzone/utils'

const Corrections = props => props.corrections && props.corrections.length > 0 ?
  <table className="table">
    <thead>
      <tr>
        <th></th>
        <th>{trans('drop_author', {}, 'dropzone')}</th>
        <th>{trans('start_date', {}, 'platform')}</th>
        <th>{trans('last_edition_date', {}, 'dropzone')}</th>
        <th>{trans('finished', {}, 'dropzone')}</th>
        <th>{trans('end_date', {}, 'platform')}</th>
        <th>{trans('score', {}, 'platform')}</th>
      </tr>
    </thead>
    <tbody>
      {props.corrections.map(c =>
        <tr key={`corrector-correction-${c.id}`}>
          <td>{c.correctionDenied ? <span className='fa fa-fw fa-warning'/> : ''}</td>
          <td>{props.dropzone.parameters.dropType === constants.DROP_TYPE_TEAM ? c.dropTeam : c.dropUser}</td>
          <td>{moment(c.startDate).format('YYYY-MM-DD HH:mm')}</td>
          <td>{moment(c.lastEditionDate).format('YYYY-MM-DD HH:mm')}</td>
          <td>{c.finished ? <span className='fa fa-fw fa-check true'/> : <span className='fa fa-fw fa-times false'/>}</td>
          <td>{c.endDate ? moment(c.endDate).format('YYYY-MM-DD HH:mm') : ''}</td>
          <td>{c.score ? `${c.score} / ${props.dropzone.parameters.scoreMax}` : '-'}</td>
        </tr>
      )}
    </tbody>
  </table> :
  <div className="alert alert-warning">
    {trans('no_correction', {}, 'dropzone')}
  </div>

Corrections.propTypes = {
  dropzone: T.shape(DropzoneType.propTypes).isRequired,
  corrections: T.arrayOf(T.shape(CorrectionType.propTypes))
}

const Corrector = props => !props.drop || !props.corrections ?
  <span className="fa fa-fw fa-circle-notch fa-spin"></span> :
  <div id="corrector-container">
    <h2>
      {trans(
        'corrections_list_from',
        {'name': props.dropzone.parameters.dropType === constants.DROP_TYPE_USER ?
          `${props.drop.user.firstName} ${props.drop.user.lastName}` :
          props.drop.teamName
        },
        'dropzone'
      )}
    </h2>
    <Corrections
      corrections={props.corrections[getCorrectionKey(props.drop, props.dropzone)] || []}
      dropzone={props.dropzone}
    />
  </div>

Corrector.propTypes = {
  dropzone: T.shape(DropzoneType.propTypes).isRequired,
  drop: T.shape(DropType.propTypes),
  corrections: T.oneOfType([T.object, T.array])
}

function mapStateToProps(state) {
  return {
    dropzone: selectors.dropzone(state),
    drop: selectors.correctorDrop(state),
    corrections: selectors.corrections(state)
  }
}

function mapDispatchToProps() {
  return {}
}

const ConnectedCorrector = connect(mapStateToProps, mapDispatchToProps)(Corrector)

export {ConnectedCorrector as Corrector}