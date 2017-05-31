import React from 'react'
import {PropTypes as T} from 'prop-types'

import {connect} from 'react-redux'

import quizSelect from './../../selectors'
import {select as resourceSelect} from '#/main/core/layout/resource/selectors'
import {selectors as paperSelect} from './../selectors'
import {tex, t} from '#/main/core/translation'
import {ScoreBox} from './../../../items/components/score-box.jsx'
import {utils} from './../utils'

export const PaperRow = props =>
  <tr>
    {props.admin &&
      <td>{props.user ? props.user.name:t('anonymous')}</td>
    }
    <td>{props.number}</td>
    <td>
      <small className="text-muted">{props.startDate}</small>
    </td>
    <td>
      <small className="text-muted">{props.endDate || '-'}</small>
    </td>
    <td className="text-center">
      <span className="sr-only">{tex(props.finished ? 'yes' : 'no')}</span>
      {props.finished && <span className="fa fa-fw fa-check" />}
    </td>
    <td className="text-right">
      {props.showScore ?
        props.score || 0 === props.score ? <ScoreBox size="sm" score={props.score} scoreMax={props.scoreMax} /> : '-'
        :
        tex('paper_score_not_available')
      }
    </td>
    <td className="text-right table-actions">
      <a href={`#papers/${props.id}`} disabled={!props.showCorrection} className="btn btn-link">
        <span className="fa fa-fw fa-eye" />
      </a>
    </td>
  </tr>

PaperRow.propTypes = {
  admin: T.bool.isRequired,
  id: T.string.isRequired,
  user: T.shape({
    name: T.string.isRequired
  }),
  number: T.number.isRequired,
  startDate: T.string.isRequired,
  endDate: T.string,
  finished: T.bool.isRequired,
  score: T.number,
  scoreMax: T.number,
  showScore: T.bool.isRequired,
  showCorrection: T.bool.isRequired
}

let Papers = props =>
  <div className="papers-list">
    <table className="table table-striped table-hover">
      <thead>
        <tr>
          {props.admin &&
            <th>{tex('paper_list_table_user')}</th>
          }
          <th>{tex('paper_list_table_paper_number')}</th>
          <th>{tex('paper_list_table_start_date')}</th>
          <th>{tex('paper_list_table_end_date')}</th>
          <th>{tex('paper_finished')}</th>
          <th>{tex('paper_list_table_score')}</th>
          <th><span className="sr-only">{tex('actions')}</span></th>
        </tr>
      </thead>
      <tbody>
        {Object.keys(props.papers).map((paperId) => {
          const paper = props.papers[paperId]

          return (
              <PaperRow
                key={paperId}
                admin={props.admin}
                {...paper}
                showScore={utils.showScore(props.admin, paper.finished, paperSelect.showScoreAt(paper), paperSelect.showCorrectionAt(paper), paperSelect.correctionDate(paper))}
                showCorrection={utils.showCorrection(props.admin, paper.finished, paperSelect.showCorrectionAt(paper), paperSelect.correctionDate(paper))}
                scoreMax={paperSelect.paperScoreMax(paper)}
              />
            )
        })}
      </tbody>
    </table>
  </div>

Papers.propTypes = {
  admin: T.bool.isRequired,
  papers: T.object.isRequired
}

function mapStateToProps(state) {
  return {
    admin: resourceSelect.editable(state) || quizSelect.papersAdmin(state),
    papers: paperSelect.papers(state)
  }
}

const ConnectedPapers = connect(mapStateToProps)(Papers)

export {ConnectedPapers as Papers}
