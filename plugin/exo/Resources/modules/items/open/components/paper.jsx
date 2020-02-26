import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ContentHtml} from '#/main/app/content/components/html'
import {trans} from '#/main/app/intl/translation'
import {PaperTabs} from '#/plugin/exo/items/components/paper-tabs'
import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'

const OpenPaper = props =>
  <PaperTabs
    id={props.item.id}
    showYours={props.showYours}
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
          <ContentHtml>{props.answer}</ContentHtml>
          :
          <div className="no-answer">{trans('no_answer', {}, 'quiz')}</div>
        }
      </div>
    }
  />

OpenPaper.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string
  }).isRequired,
  answer: T.string,
  feedback: T.string,
  showYours: T.bool.isRequired
}

export {
  OpenPaper
}
