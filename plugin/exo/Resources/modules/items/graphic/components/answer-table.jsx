import React, {PropTypes as T} from 'react'
import classes from 'classnames'
import tinycolor from 'tinycolor2'
import {tex, transChoice} from './../../../utils/translate'
import {SHAPE_RECT} from './../enums'
import {HoverFeedback} from './../../../components/form/hover-feedback.jsx'

export const AnswerTable = props =>
  <div className="answers-table">
    <h3 className="title">{tex('your_answers')}</h3>
    {props.areas.map((area, idx) =>
      <div key={area.id} className={classes('answer-row', {
        'bg-success': props.highlightScore && area.score > 0,
        'bg-danger': props.highlightScore && area.score <= 0
      })}>
        <span className="info-block">
          <span><strong>{idx + 1}</strong></span>
          <span style={{
            display: 'inline-block',
            width: '24px',
            height: '24px',
            backgroundColor: tinycolor(area.color).lighten(20).toString(),
            border: `solid 1px ${area.color}`,
            borderRadius: area.shape === SHAPE_RECT ? 0 : '12px'
          }}/>
        {props.highlightScore &&
          <span className={classes('fa', 'area-status-icon', {
            'fa-check text-success': area.score > 0,
            'fa-times text-danger': area.score <= 0
          })}/>
        }
        </span>
        <span className="info-block">
          {area.feedback &&
            <HoverFeedback
              id={`${area.id}-popover`}
              feedback={area.feedback}
            />
          }
          <span className={classes('score', {
            'text-success': props.highlightScore && area.score > 0,
            'text-danger': props.highlightScore && area.score <= 0
          })}>
            {transChoice('solution_score', area.score, {score: area.score}, 'ujm_exo')}
          </span>
        </span>
      </div>
    )}
  </div>

AnswerTable.propTypes = {
  highlightScore: T.bool.isRequired,
  areas: T.arrayOf(T.shape({
    id: T.string.isRequired,
    score: T.number,
    color: T.string.isRequired,
    shape: T.string.isRequired,
    feedback: T.string
  })).isRequired
}
