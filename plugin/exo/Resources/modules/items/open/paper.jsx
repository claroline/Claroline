import React from 'react'
import {PropTypes as T} from 'prop-types'

import {tex} from '#/main/core/translation'
import {PaperTabs} from '../components/paper-tabs.jsx'
import {Feedback} from '../components/feedback-btn.jsx'

export const OpenPaper = props => {
  return (
    <PaperTabs
      id={props.item.id}
      hideExpected={true}
      yours={
        <div className="open-paper">
          {props.feedback &&
            <div className="pull-right">
                <Feedback
                  id={props.item.id}
                  feedback={props.feedback}
                />
            </div>
          }

          {props.answer && 0 !== props.answer.length ?
            <div dangerouslySetInnerHTML={{__html: props.answer}} />
            : <div className="no-answer">{tex('no_answer')}</div>
          }
        </div>
      }
    />
  )
}
OpenPaper.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string
  }).isRequired,
  answer: T.string,
  feedback: T.string
}
