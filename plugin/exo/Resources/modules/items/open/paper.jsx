import React, {PropTypes as T} from 'react'
import {PaperTabs} from '../components/paper-tabs.jsx'
import {Feedback} from '../components/feedback-btn.jsx'

export const OpenPaper = props => {
  return (
    <PaperTabs
      id={props.item.id}
      hideExpected={true}
      yours={
        <div className="open-paper">
          {props.answerObject && props.answerObject.feedback &&
            <div className="row open-paper-feedback">
              <span className="pull-right">
                <Feedback
                  id={props.answerObject.id}
                  feedback={props.answerObject.feedback}
                />
              </span>
            </div>
          }
          <div className="open-paper-data" dangerouslySetInnerHTML={{__html: props.answer}}>
          </div>
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
  answerObject: T.object
}
